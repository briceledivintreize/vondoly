<?php
/**
 * Définition déclarative des rubriques de l'administration.
 * Ajouter un champ ou une rubrique = ajouter quelques lignes ici ;
 * listes, formulaires, tri, masquage et traductions sont générés.
 *
 * Types de champ : text, textarea, rich, number, select, relation, checkbox, image, url, date, email, password
 * Options : label, help, required, col ('half'|'third'), options, source [table, colonne], ratio, focus, tr (traduisible)
 */
declare(strict_types=1);

function news_types(): array
{
    return ['parution' => 'Parution', 'dedicace' => 'Dédicace', 'salon' => 'Salon', 'apparition' => 'Apparition publique', 'media' => 'Média / entretien'];
}

function collections(): array
{
    return [
        'books' => [
            'table' => 'books', 'label' => 'Livres', 'one' => 'un livre', 'icon' => 'book', 'group' => 'Contenus',
            'order' => 'year DESC, sort, id DESC', 'title' => 'title', 'slug_from' => 'title', 'public' => '/oeuvres/{slug}',
            'list' => ['cover_id' => 'thumb', 'title' => 'title', 'year' => 'Année', 'publisher_id' => 'Éditeur'],
            'search' => ['title', 'subtitle', 'year'],
            'fields' => [
                ['name' => 'title', 'label' => 'Titre', 'type' => 'text', 'required' => true, 'tr' => true],
                ['name' => 'subtitle', 'label' => 'Sous-titre', 'type' => 'text', 'tr' => true],
                ['name' => 'cover_id', 'label' => 'Couverture (recto)', 'type' => 'image', 'ratio' => null, 'col' => 'half',
                    'help' => 'Envoyez la meilleure version disponible : elle remplace l’ancienne sans toucher au reste de la fiche. Sans image, une carte provisoire s’affiche.'],
                ['name' => 'back_id', 'label' => 'Couverture (verso)', 'type' => 'image', 'ratio' => null, 'col' => 'half', 'help' => 'Facultatif. Permet de « retourner » le livre sur la fiche.'],
                ['name' => 'cover_alt', 'label' => 'Texte alternatif de la couverture', 'type' => 'text', 'help' => 'Décrit l’image pour les personnes malvoyantes. Par défaut : « Couverture du livre … ».'],
                ['name' => 'genre_id', 'label' => 'Genre (filtre)', 'type' => 'select', 'source' => ['genres', 'name'], 'col' => 'third'],
                ['name' => 'genre_label', 'label' => 'Genre affiché (exact)', 'type' => 'text', 'col' => 'third', 'help' => 'Ex. « Anthologie de poèmes ». Vide = nom du genre.', 'tr' => true],
                ['name' => 'year', 'label' => 'Année', 'type' => 'number', 'required' => true, 'col' => 'third'],
                ['name' => 'edition_num', 'label' => 'Édition', 'type' => 'select', 'options' => [1 => '1re édition', 2 => '2e édition', 3 => '3e édition', 4 => '4e édition'], 'col' => 'third'],
                ['name' => 'other_edition_id', 'label' => 'Lien « Autre édition »', 'type' => 'relation', 'source' => ['books', "title || ' (' || year || ')'"], 'col' => 'third', 'help' => 'Relie les deux éditions d’un même livre (le lien est créé dans les deux sens).'],
                ['name' => 'publisher_id', 'label' => 'Éditeur', 'type' => 'select', 'source' => ['publishers', 'name'], 'col' => 'third'],
                ['name' => 'place', 'label' => 'Lieu d’édition', 'type' => 'text', 'col' => 'third'],
                ['name' => 'collection', 'label' => 'Collection', 'type' => 'text', 'col' => 'third'],
                ['name' => 'pages', 'label' => 'Nombre de pages', 'type' => 'text', 'col' => 'third'],
                ['name' => 'preface', 'label' => 'Préface', 'type' => 'text', 'col' => 'half'],
                ['name' => 'postface', 'label' => 'Postface', 'type' => 'text', 'col' => 'half'],
                ['name' => 'isbn', 'label' => 'ISBN / EAN', 'type' => 'text', 'col' => 'half'],
                ['name' => 'publisher_link', 'label' => 'Lien vers le livre chez l’éditeur', 'type' => 'url', 'col' => 'half'],
                ['name' => 'summary', 'label' => 'Résumé', 'type' => 'rich', 'tr' => true, 'help' => 'Facultatif : sans résumé, la fiche s’affiche quand même.'],
                ['name' => 'is_collective', 'label' => 'Ouvrage collectif', 'type' => 'checkbox', 'col' => 'third'],
                ['name' => 'special_note', 'label' => 'Mention particulière', 'type' => 'text', 'col' => 'third', 'help' => 'Ex. « Ouvrage collectif, pages 119-126 ».', 'tr' => true],
                ['name' => 'featured', 'label' => 'Mettre en avant sur l’accueil (« Dernier livre »)', 'type' => 'checkbox', 'col' => 'third'],
                ['name' => 'slug', 'label' => 'Adresse de la page', 'type' => 'slug', 'help' => 'Générée à partir du titre. À ne modifier qu’en cas de besoin.'],
            ],
        ],
        'news' => [
            'table' => 'news', 'label' => 'Actualités', 'one' => 'une actualité', 'icon' => 'news', 'group' => 'Contenus',
            'order' => 'date DESC, id DESC', 'title' => 'title', 'slug_from' => 'title', 'public' => '/actualites/{slug}', 'module' => 'actualites',
            'list' => ['image_id' => 'thumb', 'title' => 'title', 'date' => 'Date', 'type' => 'Type'],
            'search' => ['title', 'place'],
            'fields' => [
                ['name' => 'title', 'label' => 'Titre', 'type' => 'text', 'required' => true, 'tr' => true],
                ['name' => 'type', 'label' => 'Type', 'type' => 'select', 'options' => news_types(), 'col' => 'third'],
                ['name' => 'date', 'label' => 'Date', 'type' => 'date', 'required' => true, 'col' => 'third'],
                ['name' => 'end_date', 'label' => 'Date de fin (facultatif)', 'type' => 'date', 'col' => 'third'],
                ['name' => 'place', 'label' => 'Lieu', 'type' => 'text', 'col' => 'half', 'tr' => true],
                ['name' => 'is_appearance', 'label' => 'Afficher dans « Prochaine apparition » sur l’accueil tant que la date n’est pas passée', 'type' => 'checkbox', 'col' => 'half'],
                ['name' => 'image_id', 'label' => 'Image (16:9)', 'type' => 'image', 'ratio' => '16:9', 'help' => 'Facultative. Recadrée automatiquement au format 16:9.'],
                ['name' => 'excerpt', 'label' => 'Chapeau (résumé court)', 'type' => 'textarea', 'tr' => true],
                ['name' => 'body', 'label' => 'Texte', 'type' => 'rich', 'tr' => true],
                ['name' => 'link', 'label' => 'Lien externe (facultatif)', 'type' => 'url'],
                ['name' => 'slug', 'label' => 'Adresse de la page', 'type' => 'slug'],
            ],
        ],
        'timeline' => [
            'table' => 'timeline', 'label' => 'Frise chronologique', 'one' => 'un jalon', 'icon' => 'timeline', 'group' => 'Parcours',
            'order' => 'sort, id', 'title' => 'year_label', 'sortable' => true, 'module' => 'parcours',
            'list' => ['year_label' => 'title', 'text' => 'Jalon'],
            'fields' => [
                ['name' => 'year_label', 'label' => 'Date (ex. 2011, 2020-2021)', 'type' => 'text', 'required' => true, 'col' => 'third'],
                ['name' => 'title', 'label' => 'Titre court (facultatif)', 'type' => 'text', 'col' => 'half', 'tr' => true],
                ['name' => 'text', 'label' => 'Jalon', 'type' => 'rich', 'required' => true, 'tr' => true],
            ],
        ],
        'associations' => [
            'table' => 'associations', 'label' => 'Engagements', 'one' => 'un engagement', 'icon' => 'users', 'group' => 'Parcours',
            'order' => 'sort, id', 'title' => 'name', 'sortable' => true, 'module' => 'parcours',
            'list' => ['name' => 'title', 'role' => 'Rôle', 'since' => 'Depuis'],
            'fields' => [
                ['name' => 'name', 'label' => 'Nom de l’association / du mouvement', 'type' => 'text', 'required' => true, 'tr' => true],
                ['name' => 'role', 'label' => 'Rôle', 'type' => 'text', 'col' => 'half', 'tr' => true],
                ['name' => 'since', 'label' => 'Depuis', 'type' => 'text', 'col' => 'half'],
                ['name' => 'text', 'label' => 'Présentation', 'type' => 'rich', 'tr' => true],
                ['name' => 'url', 'label' => 'Site web', 'type' => 'url'],
            ],
        ],
        'distinctions' => [
            'table' => 'distinctions', 'label' => 'Distinctions', 'one' => 'une distinction', 'icon' => 'award', 'group' => 'Reconnaissance',
            'order' => "scope DESC, sort, year DESC", 'title' => 'label', 'sortable' => true,
            'list' => ['image_id' => 'thumb', 'label' => 'title', 'year' => 'Année', 'scope' => 'Catégorie'],
            'fields' => [
                ['name' => 'scope', 'label' => 'Catégorie', 'type' => 'select', 'options' => ['personal' => 'Distinction personnelle', 'house' => 'Distinction de la maison d’édition'], 'col' => 'half'],
                ['name' => 'status', 'label' => 'Intitulé du statut', 'type' => 'text', 'col' => 'half', 'help' => 'Exact : Lauréat, Finaliste, Nommé, Mention… Ne jamais écrire « lauréat » sans preuve.', 'tr' => true],
                ['name' => 'label', 'label' => 'Intitulé exact de la distinction', 'type' => 'text', 'required' => true, 'tr' => true],
                ['name' => 'year', 'label' => 'Année', 'type' => 'text', 'col' => 'third'],
                ['name' => 'source', 'label' => 'Source', 'type' => 'text', 'col' => 'third'],
                ['name' => 'source_url', 'label' => 'Lien de la source', 'type' => 'url', 'col' => 'third'],
                ['name' => 'image_id', 'label' => 'Photo (facultative)', 'type' => 'image', 'ratio' => '3:2'],
                ['name' => 'caption', 'label' => 'Légende de la photo', 'type' => 'text', 'tr' => true],
            ],
        ],
        'press' => [
            'table' => 'press', 'label' => 'Presse et entretiens', 'one' => 'un article', 'icon' => 'press', 'group' => 'Reconnaissance',
            'order' => 'sort, date_sort DESC', 'title' => 'title', 'sortable' => true,
            'list' => ['title' => 'title', 'media_name' => 'Média', 'date_label' => 'Date'],
            'search' => ['title', 'media_name'],
            'fields' => [
                ['name' => 'media_name', 'label' => 'Média', 'type' => 'text', 'required' => true, 'col' => 'half'],
                ['name' => 'date_label', 'label' => 'Date affichée (ex. 19 juin 2022)', 'type' => 'text', 'col' => 'third'],
                ['name' => 'date_sort', 'label' => 'Date (pour le tri)', 'type' => 'date', 'col' => 'third'],
                ['name' => 'title', 'label' => 'Titre de l’article', 'type' => 'text', 'required' => true, 'tr' => true],
                ['name' => 'excerpt', 'label' => 'Extrait', 'type' => 'textarea', 'tr' => true],
                ['name' => 'url', 'label' => 'Lien vers l’article', 'type' => 'url'],
            ],
        ],
        'testimonials' => [
            'table' => 'testimonials', 'label' => 'Témoignages', 'one' => 'un témoignage', 'icon' => 'quote', 'group' => 'Reconnaissance',
            'order' => 'sort, id', 'title' => 'name', 'sortable' => true,
            'list' => ['name' => 'title', 'quote' => 'Citation'],
            'fields' => [
                ['name' => 'quote', 'label' => 'Citation (texte exact)', 'type' => 'textarea', 'required' => true, 'tr' => true],
                ['name' => 'name', 'label' => 'Nom', 'type' => 'text', 'required' => true, 'col' => 'third'],
                ['name' => 'role', 'label' => 'Fonction', 'type' => 'text', 'col' => 'third', 'tr' => true],
                ['name' => 'book', 'label' => 'À propos de (livre)', 'type' => 'text', 'col' => 'third'],
            ],
        ],
        'embeds' => [
            'table' => 'embeds', 'label' => 'Vidéos et audios', 'one' => 'une vidéo ou un audio', 'icon' => 'play', 'group' => 'Reconnaissance',
            'order' => 'sort, id', 'title' => 'title', 'sortable' => true,
            'list' => ['title' => 'title', 'url' => 'Lien'],
            'fields' => [
                ['name' => 'title', 'label' => 'Titre', 'type' => 'text', 'required' => true, 'tr' => true],
                ['name' => 'url', 'label' => 'Lien YouTube ou SoundCloud', 'type' => 'url', 'required' => true, 'help' => 'Collez simplement l’adresse de la vidéo ou du son : le lecteur est intégré automatiquement.'],
                ['name' => 'date_label', 'label' => 'Date', 'type' => 'text', 'col' => 'half'],
                ['name' => 'description', 'label' => 'Description', 'type' => 'textarea', 'tr' => true],
            ],
        ],
        'house_figures' => [
            'table' => 'house_figures', 'label' => 'Chiffres de la maison', 'one' => 'un chiffre', 'icon' => 'chart', 'group' => 'L’éditeur', 'module' => 'editeur',
            'order' => 'sort, id', 'title' => 'label', 'sortable' => true,
            'list' => ['value' => 'title', 'label' => 'Libellé'],
            'fields' => [
                ['name' => 'value', 'label' => 'Chiffre (ex. 130+)', 'type' => 'text', 'required' => true, 'col' => 'third'],
                ['name' => 'label', 'label' => 'Libellé', 'type' => 'text', 'required' => true, 'tr' => true],
            ],
        ],
        'house_collections' => [
            'table' => 'house_collections', 'label' => 'Collections de la maison', 'one' => 'une collection', 'icon' => 'layers', 'group' => 'L’éditeur', 'module' => 'editeur',
            'order' => 'sort, id', 'title' => 'name', 'sortable' => true,
            'list' => ['name' => 'title', 'text' => 'Description'],
            'fields' => [
                ['name' => 'name', 'label' => 'Nom de la collection', 'type' => 'text', 'required' => true],
                ['name' => 'text', 'label' => 'Description', 'type' => 'textarea', 'tr' => true],
            ],
        ],
        'photos' => [
            'table' => 'photos', 'label' => 'Photos du site', 'one' => 'une photo', 'icon' => 'image', 'group' => 'Médias',
            'order' => 'sort, id', 'title' => 'caption', 'sortable' => true, 'scoped' => 'slot',
            'list' => ['media_id' => 'thumb', 'caption' => 'title', 'year' => 'Année'],
            'fields' => [
                ['name' => 'media_id', 'label' => 'Photo', 'type' => 'image', 'ratio' => 'slot'],
                ['name' => 'caption', 'label' => 'Légende', 'type' => 'text', 'tr' => true],
                ['name' => 'year', 'label' => 'Année', 'type' => 'text', 'col' => 'third'],
            ],
        ],
        'social_links' => [
            'table' => 'social_links', 'label' => 'Réseaux sociaux', 'one' => 'un lien', 'icon' => 'link', 'group' => 'Réglages', 'admin' => true,
            'order' => 'sort, id', 'title' => 'network', 'sortable' => true,
            'list' => ['network' => 'title', 'url' => 'Lien'],
            'fields' => [
                ['name' => 'network', 'label' => 'Réseau', 'type' => 'select', 'col' => 'half', 'options' => ['x' => 'X (Twitter)', 'facebook' => 'Facebook', 'linkedin' => 'LinkedIn', 'instagram' => 'Instagram', 'youtube' => 'YouTube', 'tiktok' => 'TikTok', 'email' => 'Adresse e-mail', 'link' => 'Autre lien (blog, chaîne…)']],
                ['name' => 'label', 'label' => 'Libellé affiché (facultatif)', 'type' => 'text', 'col' => 'half'],
                ['name' => 'url', 'label' => 'Adresse (lien complet ou e-mail)', 'type' => 'text', 'help' => 'Un réseau sans lien n’apparaît pas sur le site. Les liens s’ouvrent dans un nouvel onglet.'],
                ['name' => 'in_footer', 'label' => 'Afficher aussi dans le pied de page', 'type' => 'checkbox'],
            ],
        ],
        'genres' => [
            'table' => 'genres', 'label' => 'Genres', 'one' => 'un genre', 'icon' => 'tag', 'group' => 'Listes', 'no_visible' => true,
            'order' => 'sort, name', 'title' => 'name', 'sortable' => true, 'slug_from' => 'name',
            'list' => ['name' => 'title'],
            'fields' => [['name' => 'name', 'label' => 'Nom du genre', 'type' => 'text', 'required' => true], ['name' => 'slug', 'label' => 'Identifiant', 'type' => 'slug']],
        ],
        'publishers' => [
            'table' => 'publishers', 'label' => 'Éditeurs', 'one' => 'un éditeur', 'icon' => 'building', 'group' => 'Listes', 'no_visible' => true,
            'order' => 'sort, name', 'title' => 'name', 'sortable' => true, 'slug_from' => 'name',
            'list' => ['name' => 'title', 'city' => 'Ville'],
            'fields' => [
                ['name' => 'name', 'label' => 'Nom de l’éditeur', 'type' => 'text', 'required' => true],
                ['name' => 'city', 'label' => 'Ville', 'type' => 'text', 'col' => 'half'],
                ['name' => 'url', 'label' => 'Site web', 'type' => 'url', 'col' => 'half'],
                ['name' => 'slug', 'label' => 'Identifiant', 'type' => 'slug'],
            ],
        ],
        'users' => [
            'table' => 'users', 'label' => 'Utilisateurs', 'one' => 'un utilisateur', 'icon' => 'user', 'group' => 'Réglages', 'admin' => true, 'no_visible' => true,
            'order' => 'role, name', 'title' => 'name',
            'list' => ['name' => 'title', 'email' => 'E-mail', 'role' => 'Rôle', 'last_login' => 'Dernière connexion'],
            'fields' => [
                ['name' => 'name', 'label' => 'Nom', 'type' => 'text', 'required' => true, 'col' => 'half'],
                ['name' => 'email', 'label' => 'E-mail (identifiant)', 'type' => 'email', 'required' => true, 'col' => 'half'],
                ['name' => 'role', 'label' => 'Rôle', 'type' => 'select', 'options' => ['editor' => 'Éditeur de contenu (textes et images)', 'admin' => 'Administrateur (tout, y compris réglages)'], 'col' => 'half'],
                ['name' => 'active', 'label' => 'Compte actif', 'type' => 'checkbox', 'col' => 'half'],
                ['name' => 'password', 'label' => 'Mot de passe', 'type' => 'password', 'help' => '12 caractères minimum. Laisser vide pour ne pas le changer.'],
            ],
        ],
    ];
}

/** Rubriques ayant un champ « note interne » (points à valider, jamais publiés). */
function has_note(string $table): bool
{
    return in_array($table, ['books', 'timeline', 'associations', 'distinctions', 'press', 'testimonials', 'embeds', 'news', 'photos'], true);
}
