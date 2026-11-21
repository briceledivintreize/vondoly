# Site d’auteur de Sébastien Vondoly — socle « Plume »

Site vitrine d’auteur avec catalogue de livres, commande par WhatsApp et administration complète.
Conçu comme **modèle réutilisable** : le code est identique d’un auteur à l’autre, tout le reste
(textes, couleurs, polices, modules, liens, numéro WhatsApp…) se règle dans l’administration.

## En bref

| Élément | Choix | Pourquoi |
|---|---|---|
| Serveur | PHP 8.2+ sans framework, SQLite | Fonctionne sur n’importe quel hébergement mutualisé, aucune base MySQL à gérer, une sauvegarde = un fichier |
| Images | GD : recadrage par emplacement, AVIF + WebP + JPEG, jamais agrandies | Site léger sur mobile (section 3.5 et 9) |
| Animations | GSAP 3.15 (ScrollTrigger, SplitText, Flip, DrawSVG), Lenis | Défilement doux, titres révélés ligne par ligne, frise horizontale, filtres animés |
| 3D | Shader WebGL natif dans le hero (≈ 3 Ko) ; Three.js pour la bibliothèque 3D, **chargé à la demande** | Effet « encre et ocre » sans alourdir le premier affichage |
| Transitions | View Transitions API (couverture qui se « déplace » de la grille vers la fiche) | Aucune bibliothèque, dégradation propre |
| PDF | dompdf (dossier de presse généré depuis les contenus) | Aucune mise en page à refaire |
| Accessibilité | « Réduire les animations » respecté partout, clavier, focus visibles, ARIA | Section 11 |

Aucune étape de compilation : les bibliothèques sont copiées dans `public/assets/vendor/`.

## Arborescence

```
site-vondoly/
├─ public/              ← racine web (DocumentRoot)
│  ├─ index.php         contrôleur frontal (URLs propres)
│  ├─ assets/           css, js, polices, bibliothèques (gsap, three, lenis)
│  └─ uploads/          images traitées (générées)
├─ app/
│  ├─ lib/              base, textes, contenus, images, auth, statistiques
│  ├─ admin/            administration : rubriques déclaratives + moteur générique
│  ├─ views/            gabarits du site, de l’admin et du dossier de presse
│  ├─ pages.php         pages publiques, SEO, sitemap, dossier de presse
│  ├─ install.php       assistant « nouveau site d’auteur »
│  └─ schema.sql        structure de la base
├─ data/                base SQLite, sauvegardes, cache (à protéger, hors web)
├─ install/seed-vondoly.php   chargement initial du contenu de S. Vondoly
├─ vendor/              dompdf (composer)
├─ tooling/             outils de développement (npm) — ne pas déployer
└─ docs/                guides
```

## Lancer en local

Le dépôt ne contient ni la base de données, ni les images générées, ni les comptes :
ils sont créés à l’installation (`data/` et `public/uploads/` sont ignorés par git).

```powershell
git clone https://github.com/briceledivintreize/vondoly.git
```

```powershell
cd vondoly; composer install --no-dev
```

```powershell
php install/seed-vondoly.php
```

```powershell
php -S localhost:8080 -t public public/index.php
```

Puis ouvrir http://localhost:8080 (site) et http://localhost:8080/admin (administration).
Le script de chargement lit les couvertures et le portrait dans `install/medias/`, traite les
images et écrit les identifiants initiaux dans `data/identifiants-initiaux.txt` : les changer à la
première connexion (« Mon compte ») puis supprimer ce fichier.
Pour un autre auteur, ne pas lancer ce script : ouvrir le site, l’assistant d’installation s’affiche.

Recréer la base à partir de zéro (l’ancienne est sauvegardée) :

```powershell
php install/seed-vondoly.php --force
```

## Mise en ligne (hébergement DTC)

1. **Prérequis** : PHP 8.2 ou plus avec les extensions `pdo_sqlite`, `gd` (avec WebP et AVIF si possible), `intl`, `mbstring`, `zip`, `fileinfo`.
2. Envoyer `app/`, `data/`, `install/`, `public/` et `vendor/` (après `composer install --no-dev`), mais pas `tooling/`.
3. Pointer le domaine `sebastienvondoly.com` sur le dossier `public/`.
   Si l’hébergeur ne le permet pas, envoyer aussi le `.htaccess` de la racine : il redirige vers `public/` et bloque l’accès aux autres dossiers.
4. Donner les droits d’écriture au serveur web sur `data/` et `public/uploads/`.
5. Activer le certificat HTTPS ; `public/.htaccess` force ensuite la redirection vers HTTPS.
6. Vérifier dans l’administration › Réglages › Identité : nom de domaine et e-mail de réception.
7. Déclarer `https://sebastienvondoly.com/sitemap.xml` dans Google Search Console.

Nginx : `try_files $uri /index.php?$query_string;` et refuser l’exécution PHP dans `/uploads/`.

**Envoi des e-mails du formulaire** : le site utilise la fonction `mail()` de PHP. Si l’hébergeur
ne l’autorise pas, les messages restent consultables dans l’administration (rubrique Messages) :
aucun message n’est perdu.

## Sécurité

- Mots de passe hachés (bcrypt/argon selon PHP), sessions `HttpOnly` + `SameSite`, expiration après 8 h.
- Jeton CSRF sur tous les formulaires, limitation des tentatives de connexion (8 / 15 min).
- Formulaire de contact : champ piège, délai minimal, 5 envois/heure par adresse.
- Images : type vérifié par le contenu, image **ré-encodée** (aucun fichier envoyé n’est servi tel quel), exécution de scripts interdite dans `uploads/`.
- En-têtes : HSTS, `nosniff`, `Referrer-Policy`, `X-Frame-Options`, `Permissions-Policy`.
- Statistiques sans cookie : empreinte journalière salée et irréversible, aucune IP stockée.

## Sauvegardes

Automatiques chaque jour (base + images, 14 conservées) à la première visite de l’administration,
et à la demande (Réglages › Sauvegardes). Restauration : décompresser l’archive et remplacer
`data/site.sqlite` et `public/uploads/`.

## Mettre à jour les bibliothèques

```powershell
cd tooling; npm update
```

puis recopier `gsap/dist/*.min.js`, `three/build/three.module.min.js` et `lenis/dist/lenis.min.js`
dans `public/assets/vendor/`. Dompdf : `composer update`.

## Ajouter un champ ou une rubrique

Les rubriques de l’administration sont décrites dans `app/admin/collections.php`
(liste de champs typés). Ajouter une colonne dans `app/schema.sql` (et la base existante),
déclarer le champ : formulaire, liste, tri et traductions sont générés.
Les textes du site sont déclarés dans `app/lib/texts.php`.

## Points du cahier des charges laissés à valider

Ils apparaissent dans le tableau de bord de l’administration (« Points à valider ») grâce aux
notes internes des fiches : prix Sanofi 2010 (masqué), nomination Togo Top Impact (« Nommé »),
intitulé ADELF, rôle sur « La sixième décennie » (fiche masquée), date Rose Bleue (masquée),
couvertures manquantes ou en basse définition, résumés à rédiger, citations des témoignages
(masqués), accroche proposée à valider, e-mail et liens Facebook/LinkedIn à saisir, hébergeur
à compléter dans les mentions légales.
