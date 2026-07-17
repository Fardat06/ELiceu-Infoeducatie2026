import math
from pathlib import Path

import joblib
import pandas as pd
from fastapi import FastAPI, HTTPException
from pydantic import BaseModel, Field


app = FastAPI(
    title="Eliceu AI",
    description="API pentru estimarea șanselor de admitere la liceu",
    version="2.1.0"
)


MODEL_PATH = Path(__file__).resolve().parent / "model_admitere_random_forest.pkl"

if not MODEL_PATH.exists():
    raise RuntimeError(
        f"Modelul nu a fost găsit la: {MODEL_PATH}"
    )

model = joblib.load(MODEL_PATH)


class AdmitereData(BaseModel):
    medie_elev: float = Field(ge=1, le=10)
    pozitie_elev: int = Field(ge=1)

    sector: str
    profil: str
    specializare: str
    limba: str
    bilingv: str = ""

    medie_liceu: float = Field(ge=1, le=10)

    ultima_pozitie_2025: int = Field(ge=1)
    ultima_pozitie_2024: int = Field(ge=1)
    ultima_pozitie_2023: int = Field(ge=1)

    # Sunt primite pentru compatibilitate cu PHP,
    # dar sunt recalculate aici.
    pozitie_medie_intrare: int
    diferenta_pozitie: int


def clamp(value: float, minimum: float, maximum: float) -> float:
    return max(minimum, min(maximum, value))


def get_model_classes():
    """
    Extrage clasele modelului indiferent dacă este:
    - model simplu sklearn;
    - Pipeline;
    - estimator cu atribut classes_.
    """
    if hasattr(model, "classes_"):
        return list(model.classes_)

    if hasattr(model, "named_steps"):
        final_estimator = model.named_steps.get("model")

        if final_estimator is not None and hasattr(final_estimator, "classes_"):
            return list(final_estimator.classes_)

    raise ValueError("Nu am putut identifica clasele modelului.")


def get_admission_probability(probabilities) -> float:
    """
    Returnează probabilitatea pentru clasa 1 = admis.
    """
    classes = get_model_classes()

    if 1 not in classes:
        raise ValueError(
            f"Clasa 1 (admis) lipsește din model. Clase disponibile: {classes}"
        )

    admitted_index = classes.index(1)

    return float(probabilities[admitted_index])


def calculate_average_entry_position(data: AdmitereData) -> int:
    return round(
        (
            data.ultima_pozitie_2025
            + data.ultima_pozitie_2024
            + data.ultima_pozitie_2023
        ) / 3
    )


def calculate_position_score(
    student_position: int,
    average_entry_position: int
) -> float:
    """
    Scor continuu după poziție.

    Exemplu:
    - elevul este înaintea limitei istorice -> scor peste 50;
    - elevul este după limită -> scor sub 50.
    """
    difference = average_entry_position - student_position

    return 50 + 45 * math.tanh(difference / 650)


def calculate_average_score(
    student_average: float,
    school_average: float
) -> float:
    """
    Scor continuu după diferența de medie.

    Divizorul 0.25 face diferențele de medie mai importante.
    """
    difference = student_average - school_average

    return 50 + 45 * math.tanh(difference / 0.25)


def calculate_trend_score(
    position_2025: int,
    position_2024: int,
    position_2023: int
) -> float:
    """
    Dacă poziția-limită din 2025 este mai mare decât în anii anteriori,
    specializarea a devenit ceva mai accesibilă.
    """
    previous_average = (position_2024 + position_2023) / 2
    trend_difference = position_2025 - previous_average

    return 50 + 20 * math.tanh(trend_difference / 700)


def calculate_stability_score(
    position_2025: int,
    position_2024: int,
    position_2023: int
) -> float:
    """
    Specializările cu poziții foarte instabile primesc un scor puțin mai prudent.
    """
    positions = [position_2025, position_2024, position_2023]
    mean_position = sum(positions) / len(positions)

    variance = sum(
        (position - mean_position) ** 2
        for position in positions
    ) / len(positions)

    standard_deviation = math.sqrt(variance)

    return 100 - min(40, standard_deviation / 25)


def apply_logical_adjustments(
    probability: float,
    student_average: float,
    school_average: float,
    student_position: int,
    average_entry_position: int
) -> float:
    """
    Corecții logice pentru a evita situații contraintuitive.

    Media și poziția rămân ambele importante.
    """
    average_gap = student_average - school_average
    position_gap = average_entry_position - student_position

    # Penalizare dacă media elevului este sub media specializării.
    if average_gap < 0:
        probability -= min(20, abs(average_gap) * 32)
    else:
        probability += min(10, average_gap * 14)

    # Penalizare dacă poziția elevului este după limita istorică.
    if position_gap < 0:
        probability -= min(18, abs(position_gap) / 90)
    else:
        probability += min(12, position_gap / 150)

    return probability


@app.get("/")
def root():
    return {
        "status": "ok",
        "message": "Eliceu AI funcționează.",
        "version": "2.1.0"
    }


@app.post("/predict")
def predict(data: AdmitereData):
    try:
        average_entry_position = calculate_average_entry_position(data)
        position_difference = average_entry_position - data.pozitie_elev

        input_data = pd.DataFrame([{
            "medie_elev": data.medie_elev,
            "pozitie_elev": data.pozitie_elev,
            "sector": data.sector,
            "profil": data.profil,
            "specializare": data.specializare,
            "limba": data.limba,
            "bilingv": data.bilingv,
            "medie_liceu": data.medie_liceu,
            "ultima_pozitie_2025": data.ultima_pozitie_2025,
            "ultima_pozitie_2024": data.ultima_pozitie_2024,
            "ultima_pozitie_2023": data.ultima_pozitie_2023,
            "pozitie_medie_intrare": average_entry_position,
            "diferenta_pozitie": position_difference
        }])

        model_prediction = int(model.predict(input_data)[0])
        probabilities = model.predict_proba(input_data)[0]

        model_probability = (
            get_admission_probability(probabilities) * 100
        )

        position_score = calculate_position_score(
            data.pozitie_elev,
            average_entry_position
        )

        average_score = calculate_average_score(
            data.medie_elev,
            data.medie_liceu
        )

        trend_score = calculate_trend_score(
            data.ultima_pozitie_2025,
            data.ultima_pozitie_2024,
            data.ultima_pozitie_2023
        )

        stability_score = calculate_stability_score(
            data.ultima_pozitie_2025,
            data.ultima_pozitie_2024,
            data.ultima_pozitie_2023
        )

        # Formula finală:
        # - modelul rămâne important;
        # - poziția și media cântăresc mai mult decât înainte;
        # - trendul și stabilitatea au influență mică.
        final_probability = (
            0.38 * model_probability
            + 0.30 * position_score
            + 0.25 * average_score
            + 0.04 * trend_score
            + 0.03 * stability_score
        )

        final_probability = apply_logical_adjustments(
            probability=final_probability,
            student_average=data.medie_elev,
            school_average=data.medie_liceu,
            student_position=data.pozitie_elev,
            average_entry_position=average_entry_position
        )

        percentage = round(
            clamp(final_probability, 1, 99),
            1
        )

        if percentage >= 75:
            level = "șanse mari"
        elif percentage >= 50:
            level = "șanse medii"
        elif percentage >= 30:
            level = "șanse mici"
        else:
            level = "șanse reduse"

        return {
            "admis_model": model_prediction,
            "probabilitate": percentage,
            "nivel": level,

            # Câmpuri utile pentru verificare.
            # PHP-ul poate să le ignore.
            "pozitie_medie_intrare": average_entry_position,
            "diferenta_pozitie": position_difference,
            "diferenta_medie": round(
                data.medie_elev - data.medie_liceu,
                2
            ),
            "scor_model": round(model_probability, 2),
            "scor_pozitie": round(position_score, 2),
            "scor_medie": round(average_score, 2),
            "scor_tendinta": round(trend_score, 2),
            "scor_stabilitate": round(stability_score, 2)
        }

    except Exception as error:
        raise HTTPException(
            status_code=500,
            detail=f"Eroare la realizarea predicției: {error}"
        )