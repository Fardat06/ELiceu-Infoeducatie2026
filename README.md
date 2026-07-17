# ELiceu
Ǝliceu transformă alegerea liceului dintr-un proces complicat și stresant într-o experiență simplă, intuitivă și adaptată fiecărui elev de clasa a VIII-a.

Concepută ca un adevărat „magazin de licee”, platforma reunește într-un singur loc toate liceele din București și le prezintă într-un format modern, ușor de explorat. Elevii pot răsfoi carduri, aplica filtre după profil, sector, specializare sau medie, pot compara opțiuni, salva liceele preferate și accesa pagini detaliate pentru a-și construi treptat propria listă de admitere.

Elementul care diferențiază cu adevărat Ǝliceu este tehnologia din spate. Platforma integrează un predictor de admitere bazat pe Machine Learning, care analizează media și poziția elevului, alături de datele istorice ale specializărilor, pentru a estima șansele de admitere. În plus, utilizatorii au la dispoziție un test de orientare pentru identificarea profilului potrivit, o secțiune dedicată evenimentelor și noutăților, precum și alte instrumente interactive menite să îi ajute să ia decizii mai informate.

Construită pe o arhitectură modernă și protejată prin măsuri solide de securitate, precum parole criptate, autentificare în doi pași și protecție împotriva atacurilor web uzuale, Ǝliceu este mai mult decât o bază de date cu licee. Este un ghid digital inteligent, creat pentru a-i oferi fiecărui elev claritate, încredere și control asupra uneia dintre cele mai importante alegeri pentru viitorul său.

## Scop
Scopul proiectului Ǝliceu este de a sprijini elevii de clasa a VIII-a în alegerea liceului potrivit, oferindu-le într-un singur loc toate informațiile necesare pentru a lua o decizie bine fundamentată. Platforma se adresează în principal elevilor aflați în perioada admiterii la liceu, dar poate fi utilizată și de părinți sau profesori care doresc să îi îndrume în acest proces.
 Prin centralizarea informațiilor, instrumentele interactive și predicțiile bazate pe inteligență artificială, Ǝliceu își propune să reducă incertitudinea și stresul asociate admiterii și să transforme alegerea liceului într-o experiență simplă, rapidă și personalizată.


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
-	Browser web: Aplicația poate fi utilizată prin intermediul oricărui browser modern (Google Chrome, Microsoft Edge, Mozilla Firefox, Safari etc.), fără a necesita instalarea unui software suplimentar. 
-	Sistem de operare: Platforma este compatibilă cu orice sistem de operare care dispune de un browser web actualizat, precum Windows, Linux, macOS, Android sau iOS. 
-	Memorie RAM: Minimum 4 GB RAM pentru o funcționare fluentă a aplicației și afișarea optimă a conținutului. 
-	Conexiune la internet: Este necesară o conexiune stabilă la internet pentru accesarea bazei de date și comunicarea cu serviciul de inteligență artificială. 
-	Dispozitive compatibile: Platforma poate fi utilizată pe desktop, laptop, tabletă și telefon mobil, având o interfață complet responsive, adaptată automat dimensiunii ecranului. 
-	Metode de utilizare: Interfața este optimizată atât pentru utilizarea cu tastatură și mouse, cât și pentru dispozitive cu ecran tactil (touchscreen).


## Autori
- Farhat Fatima-Maria
- Cătrună Daria-Andreea
