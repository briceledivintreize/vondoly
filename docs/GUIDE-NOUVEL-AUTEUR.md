# Créer un nouveau site d’auteur à partir du socle

Le code ne change pas d’un auteur à l’autre. **Architecture retenue : un socle cloné par auteur**
(un dossier et une base SQLite par site). C’est la solution la plus simple à maintenir pour DTC :
chaque site est indépendant (sauvegarde, hébergement, nom de domaine au nom du client), une panne
ou une erreur sur un site n’affecte pas les autres, et la mise à jour du socle se fait en recopiant
`app/`, `public/assets/` et `vendor/` — jamais `data/` ni `public/uploads/`.

## Étapes (aucun code à écrire)

1. Copier le socle **sans** `data/site.sqlite`, `data/backups/`, `data/identifiants-initiaux.txt` ni le contenu de `public/uploads/`.
2. Le déposer chez l’hébergeur (voir README, « Mise en ligne »).
3. Ouvrir l’adresse du site : **l’assistant d’installation** s’affiche. Saisir nom d’usage, profil, maison d’édition (oui/non), domaine, e-mail, WhatsApp, palette, compte administrateur.
4. Se connecter à `/admin` et compléter, dans cet ordre :
   1. **Réglages** : identité, WhatsApp (vérifier l’aperçu), couleurs, modules, langues, mention de développement.
   2. **Réseaux sociaux**.
   3. **Listes** : genres et éditeurs.
   4. **Textes du site** : accroche, trois rôles, biographie, présentation de l’éditeur, mentions légales.
   5. **Photos du site** : portrait principal, portrait secondaire, image de partage, photo HD du dossier de presse.
   6. **Livres**, puis **Frise**, **Engagements**, **Distinctions**, **Presse**, **Témoignages**, **Actualités**.
   7. **Utilisateurs** : créer le compte « éditeur de contenu » de l’auteur.
5. Contrôler le site sur téléphone, puis déclarer le sitemap dans Google Search Console.

## Déterminer le profil (section 7.1)

| Question | Profil |
|---|---|
| L’écriture est-elle sa principale activité ou reconnaissance publique (journaliste, poète, romancier, éditeur) ? | **Écrivain / homme de lettres** : frise littéraire, les œuvres sont le sujet principal. |
| A-t-il une autre carrière principale et a-t-il écrit un ou plusieurs livres en plus ? | **Carrière + livre(s)** : frise de carrière, section « Ses livres » sur la page Parcours. |

Pour le profil Carrière : désactiver la bibliothèque 3D si moins de 4 livres (elle se masque
d’elle-même en dessous de 4), alléger ou désactiver **Reconnaissance**, désactiver **L’éditeur**.

## Couleurs (section 7.2)

| Cas | Méthode |
|---|---|
| L’auteur a une charte (logo, maison, couverture marquante) | En extraire 2 à 3 couleurs dominantes → Réglages › Identité visuelle |
| Pas de charte mais des préférences | Choisir 2 à 3 couleurs avec lui avant la maquette |
| Rien n’est précisé | Palette prédéfinie « Bleu nuit et or » ou « Noir et bordeaux » (un clic dans les réglages) |

Garder un contraste suffisant : texte foncé sur fond clair ; la couleur « ocre » ne sert pas au texte courant.

## Langues (section 7.3)

Réglages › Langues. Une fois une langue ajoutée, chaque texte et chaque fiche présente un champ
dans cette langue ; ce qui n’est pas traduit s’affiche dans la langue par défaut. Le sélecteur de
langue apparaît automatiquement dans l’en-tête.

## Éléments à recueillir auprès de l’auteur

**Communs à tous les profils**
- Nom d’usage et nom complet ; date et lieu de naissance, formation (facultatif pour le profil Écrivain).
- Nom de domaine, adresse e-mail publique, numéro WhatsApp des commandes.
- Liens : X, Facebook, LinkedIn, Instagram, YouTube, TikTok, autre (blog, chaîne…).
- Accroche (une phrase) ; une phrase pour chacun de ses rôles.
- Biographie validée (quelques paragraphes).
- Photos : un portrait principal (4:5), un second portrait différent, 3 à 4 photos de parcours (salons, dédicaces, archives) avec légende et année, une photo HD pour la presse.
- Pour chaque livre : couverture recto (et verso), titre exact, sous-titre, genre, année, édition, éditeur et lieu, collection, préface/postface, pages, ISBN, résumé, lien chez l’éditeur, mention particulière (collectif…).
- Distinctions : intitulé exact, statut exact, année, source.
- Presse : média, titre, date, lien ; vidéos et audios (liens YouTube/SoundCloud).
- Témoignages : citation exacte, nom, fonction.
- Mentions légales : éditeur du site, hébergeur.

**Profil Écrivain** : premiers écrits et déclic littéraire, maisons d’édition fréquentées ou fondées, rôle dans le milieu littéraire (association, revue, mouvement).

**Profil Carrière + livre(s)** : fonctions occupées et réalisations (frise), circonstances d’écriture du ou des livres.

**Maison d’édition (si l’auteur en dirige une)** : nom, présentation, chiffres, collections, distinctions de la maison, site web.
