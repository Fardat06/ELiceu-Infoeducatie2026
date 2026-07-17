import pandas as pd
import joblib

from sklearn.ensemble import RandomForestClassifier
from sklearn.compose import ColumnTransformer
from sklearn.preprocessing import OneHotEncoder
from sklearn.pipeline import Pipeline
from sklearn.model_selection import train_test_split
from sklearn.metrics import accuracy_score, classification_report

df = pd.read_csv("date_admitere_ai.csv")
df = df.fillna("")

X = df[
    [
        "medie_elev",
        "pozitie_elev",
        "sector",
        "profil",
        "specializare",
        "limba",
        "bilingv",
        "medie_liceu",
        "ultima_pozitie_2025",
        "ultima_pozitie_2024",
        "ultima_pozitie_2023",
        "pozitie_medie_intrare",
        "diferenta_pozitie"
    ]
]

y = df["admis"]

categorical_features = [
    "sector",
    "profil",
    "specializare",
    "limba",
    "bilingv"
]

numeric_features = [
    "medie_elev",
    "pozitie_elev",
    "medie_liceu",
    "ultima_pozitie_2025",
    "ultima_pozitie_2024",
    "ultima_pozitie_2023",
    "pozitie_medie_intrare",
    "diferenta_pozitie"
]

preprocessor = ColumnTransformer(
    transformers=[
        ("categorical", OneHotEncoder(handle_unknown="ignore"), categorical_features),
        ("numeric", "passthrough", numeric_features)
    ]
)

model = RandomForestClassifier(
    n_estimators=200,
    random_state=42,
    max_depth=8
)

pipeline = Pipeline(
    steps=[
        ("preprocessor", preprocessor),
        ("model", model)
    ]
)

X_train, X_test, y_train, y_test = train_test_split(
    X,
    y,
    test_size=0.2,
    random_state=42
)

pipeline.fit(X_train, y_train)

predictions = pipeline.predict(X_test)

print("Acuratețe:", accuracy_score(y_test, predictions))
print(classification_report(y_test, predictions))

joblib.dump(pipeline, "model_admitere_random_forest.pkl")

print("Modelul a fost salvat.")