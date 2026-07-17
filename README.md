# Eliceu

Ǝliceu este o platformă educațională care ajută elevii să aleagă liceul potrivit pe baza mediilor, profilurilor și specializărilor disponibile în București (folosind (broșura de admitere)[http://www.ismb.edu.ro/documente/examene/admitere/2026_2027/BROSURA_ADMITERE_2026_1.pdf] și https://static.evaluare.edu.ro/2026/) . Platforma include un chatbot AI și un sistem de Machine Learning pentru estimarea șanselor de admitere.

## Scop
Proiectul are scopul de a face procesul alegerii liceului mai simplu și mai accesibil pentru elevi.

## Rulare
Setați variabilele corect în fișireul `.env`:
```
DB_DRIVER=
DB_HOSTNAME=
DB_USERNAME=
DB_PASSWORD=
DB_DATABASE=
DB_PREFIX=
DB_ROOT_PASSWORD=
```

Prin docker:
```bash
docker compose up
```

Iar acum, platforma poate fi acesată pe http://localhost:8080


## Funcționalități
- Căutare și filtrare licee
- Informații despre specializări
- Test de orientare
- Chatbot AI pentru recomandări
- Predicții de admitere cu Random Forest
- Sistem de autentificare
- Design responsive

## Tehnologii
Frontend: HTML, CSS, JavaScript;
Backend: PHP, MySQL;
Inteligență Artificială: Python, FastAPI, Scikit-learn, Random Forest;
Hosting: Render;

## Structură
```
root
`--ai = implementare model ai alături de API-ul pentru a-l accesa
`--docker = dump-ul inițial pentru baza de date MySql
`--plugin = configurații de bază și funcții reutilizabile
`--src = sursă website (interactivitate prin javascript alături de stilizare prin css și html)
`--template = componente reutilizabile
```

### Modelul AI analizează:
- media elevului
- profilul dorit
- specializarea
- sectorul
- limba
- media liceului
și estimează probabilitatea de admitere.

## Cerințe de accesare
- Orice browser modern
- Orice sistem de operare
- RAM: 4GB
- Conexiune la internet

## Autori
- Farhat Fatima-Maria
- Cătrună Daria-Andreea
