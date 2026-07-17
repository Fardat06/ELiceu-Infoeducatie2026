import pandas as pd
import random

base = pd.read_csv("date_admitere.csv")
rows = []

for _, row in base.iterrows():
    pozitie_limita = int(row["pozitie_medie_intrare"])
    medie_liceu = float(row["medie_liceu"])

    for _ in range(80):
        diferenta = random.randint(-1200, 1200)
        pozitie_elev = max(1, pozitie_limita - diferenta)

        medie_elev = medie_liceu + (diferenta / 1200) * 0.8
        medie_elev = max(1, min(10, round(medie_elev + random.uniform(-0.12, 0.12), 2)))

        admis = 1 if pozitie_elev <= pozitie_limita else 0

        new_row = row.copy()
        new_row["medie_elev"] = medie_elev
        new_row["pozitie_elev"] = pozitie_elev
        new_row["diferenta_pozitie"] = pozitie_limita - pozitie_elev
        new_row["admis"] = admis

        rows.append(new_row)

df = pd.DataFrame(rows)
df.to_csv("date_admitere_ai.csv", index=False, encoding="utf-8-sig")

print("Am generat", len(df), "rânduri în date_admitere_ai.csv")