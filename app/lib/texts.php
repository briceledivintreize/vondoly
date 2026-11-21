<?php
/**
 * Registre des textes du site.
 *
 * Chaque texte affiché (titres, accroches, boutons, mentions) est déclaré ici
 * avec une valeur par défaut neutre ; la valeur réelle est saisie dans
 * l'administration (« Textes du site ») et stockée par langue dans la table `texts`.
 * Ajouter une langue ne demande donc aucune modification des pages.
 *
 * Format : clé => [groupe, libellé admin, type (line|text|rich), valeur par défaut]
 * Les {variables} entre accolades sont remplacées automatiquement.
 */
declare(strict_types=1);

function text_registry(): array
{
    return [
        // ---- Navigation & interface commune
        'nav.home' => ['Navigation', 'Menu : Accueil', 'line', 'Accueil'],
        'nav.parcours' => ['Navigation', 'Menu : Parcours', 'line', 'Parcours'],
        'nav.reconnaissance' => ['Navigation', 'Menu : Reconnaissance', 'line', 'Reconnaissance'],
        'nav.oeuvres' => ['Navigation', 'Menu : Œuvres', 'line', 'Œuvres'],
        'nav.editeur' => ['Navigation', 'Menu : L’éditeur', 'line', 'L’éditeur'],
        'nav.actualites' => ['Navigation', 'Menu : Actualités', 'line', 'Actualités'],
        'nav.contact' => ['Navigation', 'Menu : Contact', 'line', 'Contact'],
        'nav.menu' => ['Navigation', 'Bouton menu mobile', 'line', 'Menu'],
        'nav.close' => ['Navigation', 'Bouton fermer', 'line', 'Fermer'],
        'nav.skip' => ['Navigation', 'Lien d’évitement (accessibilité)', 'line', 'Aller au contenu'],

        'ui.want_book' => ['Boutons', 'Bouton de commande', 'line', 'Je veux ce livre'],
        'ui.see_book' => ['Boutons', 'Voir la fiche', 'line', 'Voir la fiche'],
        'ui.read_more' => ['Boutons', 'Lire la suite', 'line', 'Lire la suite'],
        'ui.see_all_books' => ['Boutons', 'Voir toutes les œuvres', 'line', 'Toute l’œuvre'],
        'ui.other_edition' => ['Boutons', 'Lien vers l’autre édition', 'line', 'Autre édition'],
        'ui.second_edition' => ['Boutons', 'Mention 2e édition', 'line', '{n}e édition'],
        'ui.first_edition' => ['Boutons', 'Mention 1re édition', 'line', '1re édition'],
        'ui.share' => ['Boutons', 'Partager', 'line', 'Partager'],
        'ui.publisher_link' => ['Boutons', 'Lien vers l’éditeur', 'line', 'Voir chez l’éditeur'],
        'ui.back' => ['Boutons', 'Retour', 'line', 'Retour'],
        'ui.flip' => ['Boutons', 'Retourner le livre', 'line', 'Voir le verso'],
        'ui.collective' => ['Boutons', 'Mention ouvrage collectif', 'line', 'Ouvrage collectif'],
        'ui.cover_soon' => ['Boutons', 'Carte provisoire : couverture à venir', 'line', 'Couverture à venir'],
        'ui.photo_soon' => ['Boutons', 'Image provisoire : photo à venir', 'line', 'Photo à venir'],
        'ui.press_kit' => ['Boutons', 'Télécharger le dossier de presse', 'line', 'Dossier de presse (PDF)'],
        'ui.load_embed' => ['Boutons', 'Charger une vidéo / un audio', 'line', 'Lire (contenu externe)'],

        // ---- Accueil
        'home.meta' => ['Accueil', 'Description pour Google (meta)', 'text', 'Site officiel de l’écrivain, journaliste et éditeur togolais.'],
        'home.kicker' => ['Accueil', 'Surtitre du hero (rôles de l’auteur)', 'line', 'Auteur'],
        'home.tagline' => ['Accueil', 'Accroche (une phrase)', 'text', 'Une voix togolaise au service des mots.'],
        'home.cta_parcours' => ['Accueil', 'Bouton « Découvrir son parcours »', 'line', 'Découvrir son parcours'],
        'home.cta_books' => ['Accueil', 'Bouton « Ses livres »', 'line', 'Ses livres'],
        'home.scroll' => ['Accueil', 'Indication de défilement', 'line', 'Défiler'],
        'home.roles_title' => ['Accueil', 'Titre : trois rôles', 'line', 'Trois manières de servir les mots'],
        'home.role1_title' => ['Accueil', 'Rôle 1 : titre', 'line', 'Écrivain'],
        'home.role1_text' => ['Accueil', 'Rôle 1 : phrase', 'text', ''],
        'home.role2_title' => ['Accueil', 'Rôle 2 : titre', 'line', 'Journaliste'],
        'home.role2_text' => ['Accueil', 'Rôle 2 : phrase', 'text', ''],
        'home.role3_title' => ['Accueil', 'Rôle 3 : titre', 'line', 'Éditeur'],
        'home.role3_text' => ['Accueil', 'Rôle 3 : phrase', 'text', ''],
        'home.figures_author_title' => ['Accueil', 'Repères : intitulé auteur', 'line', 'L’auteur'],
        'home.figures_author_1' => ['Accueil', 'Repère auteur (variables : {editions}, {debut}, {fin})', 'line', 'éditions de ses livres, de {debut} à {fin}'],
        'home.figures_author_2' => ['Accueil', 'Repère auteur 2 (variable : {titres})', 'line', 'titres distincts'],
        'home.figures_house_title' => ['Accueil', 'Repères : intitulé maison', 'line', 'Sa maison d’édition'],
        'home.figures_house_value' => ['Accueil', 'Repère maison : chiffre', 'line', '130'],
        'home.figures_house_prefix' => ['Accueil', 'Repère maison : préfixe', 'line', 'plus de'],
        'home.figures_house_label' => ['Accueil', 'Repère maison : libellé', 'line', 'ouvrages édités'],
        'home.featured_kicker' => ['Accueil', 'Surtitre : dernier livre', 'line', 'Dernier livre'],
        'home.latest_title' => ['Accueil', 'Titre : dernières parutions', 'line', 'Dernières parutions'],
        'home.library_title' => ['Accueil', 'Titre : bibliothèque 3D', 'line', 'La bibliothèque'],
        'home.library_text' => ['Accueil', 'Texte : bibliothèque 3D', 'text', 'Poésie, essais, romans, biographies et hommages : faites défiler pour parcourir l’œuvre.'],
        'home.next_title' => ['Accueil', 'Titre : prochaine apparition', 'line', 'Prochaine apparition'],
        'home.press_title' => ['Accueil', 'Titre : bandeau presse', 'line', 'Ils en parlent'],
        'home.contact_title' => ['Accueil', 'Appel à contacter : titre', 'line', 'Une invitation, un entretien, une dédicace ?'],
        'home.contact_text' => ['Accueil', 'Appel à contacter : texte', 'text', 'Journalistes, organisateurs de salons, enseignants : écrivez-lui.'],
        'home.contact_cta' => ['Accueil', 'Appel à contacter : bouton', 'line', 'Prendre contact'],

        // ---- Parcours
        'parcours.meta' => ['Parcours', 'Description pour Google (meta)', 'text', 'Biographie et parcours de l’auteur.'],
        'parcours.title' => ['Parcours', 'Titre de la page', 'line', 'Parcours'],
        'parcours.intro' => ['Parcours', 'Chapeau', 'text', ''],
        'parcours.bio' => ['Parcours', 'Biographie', 'rich', ''],
        'parcours.statement' => ['Parcours', 'Phrase de transition « plongée » (vide = masquée)', 'text', ''],
        'parcours.timeline_title' => ['Parcours', 'Titre : frise', 'line', 'Repères chronologiques'],
        'parcours.timeline_kicker' => ['Parcours', 'Surtitre : frise', 'line', 'Un parcours'],
        'parcours.timeline_hint' => ['Parcours', 'Indication de défilement (frise)', 'line', 'Faites défiler pour voyager dans le temps'],
        'parcours.assoc_title' => ['Parcours', 'Titre : associations', 'line', 'Engagements'],
        'parcours.photos_title' => ['Parcours', 'Titre : photos', 'line', 'En images'],
        'parcours.books_title' => ['Parcours', 'Titre : ses livres (profil Carrière)', 'line', 'Ses livres'],

        // ---- Reconnaissance
        'reco.meta' => ['Reconnaissance', 'Description pour Google (meta)', 'text', 'Distinctions, presse et témoignages.'],
        'reco.title' => ['Reconnaissance', 'Titre de la page', 'line', 'Reconnaissance'],
        'reco.intro' => ['Reconnaissance', 'Chapeau', 'text', ''],
        'reco.distinctions_title' => ['Reconnaissance', 'Titre : distinctions personnelles', 'line', 'Distinctions'],
        'reco.press_title' => ['Reconnaissance', 'Titre : presse', 'line', 'Presse et entretiens'],
        'reco.testimonials_title' => ['Reconnaissance', 'Titre : témoignages', 'line', 'Ils ont lu, ils témoignent'],
        'reco.media_title' => ['Reconnaissance', 'Titre : vidéos et audios', 'line', 'Écouter, regarder'],
        'reco.house_title' => ['Reconnaissance', 'Titre : distinctions de la maison', 'line', 'Distinctions de la maison d’édition'],
        'reco.presskit_text' => ['Reconnaissance', 'Texte : dossier de presse', 'text', 'Biographie, photo haute définition, bibliographie et contacts, à jour.'],

        // ---- Œuvres
        'books.meta' => ['Œuvres', 'Description pour Google (meta)', 'text', 'Toutes les œuvres de l’auteur, commandables via WhatsApp.'],
        'books.title' => ['Œuvres', 'Titre de la page', 'line', 'Œuvres'],
        'books.intro' => ['Œuvres', 'Chapeau', 'text', 'Chaque édition a sa fiche. Un clic sur « Je veux ce livre » ouvre WhatsApp avec un message prêt à envoyer.'],
        'books.search' => ['Œuvres', 'Champ de recherche', 'line', 'Rechercher un titre, un genre, une année…'],
        'books.all' => ['Œuvres', 'Filtre : tous', 'line', 'Tous'],
        'books.filter_genre' => ['Œuvres', 'Filtre : genre', 'line', 'Genre'],
        'books.filter_publisher' => ['Œuvres', 'Filtre : éditeur', 'line', 'Éditeur'],
        'books.empty' => ['Œuvres', 'Aucun résultat', 'line', 'Aucun livre ne correspond à votre recherche.'],
        'books.count' => ['Œuvres', 'Compteur (variable : {n})', 'line', '{n} éditions'],
        'books.no_summary' => ['Œuvres', 'Fiche sans résumé', 'text', 'Le résumé de ce livre sera bientôt disponible.'],
        'books.order_help' => ['Œuvres', 'Aide sous le bouton de commande', 'text', 'Paiement et livraison se règlent directement dans la conversation WhatsApp.'],
        'books.label_year' => ['Œuvres', 'Libellé : année', 'line', 'Année'],
        'books.label_publisher' => ['Œuvres', 'Libellé : éditeur', 'line', 'Éditeur'],
        'books.label_genre' => ['Œuvres', 'Libellé : genre', 'line', 'Genre'],
        'books.label_collection' => ['Œuvres', 'Libellé : collection', 'line', 'Collection'],
        'books.label_preface' => ['Œuvres', 'Libellé : préface', 'line', 'Préface'],
        'books.label_postface' => ['Œuvres', 'Libellé : postface', 'line', 'Postface'],
        'books.label_pages' => ['Œuvres', 'Libellé : pages', 'line', 'Pages'],
        'books.label_isbn' => ['Œuvres', 'Libellé : ISBN', 'line', 'ISBN / EAN'],
        'books.label_edition' => ['Œuvres', 'Libellé : édition', 'line', 'Édition'],
        'books.related' => ['Œuvres', 'Titre : autres livres', 'line', 'Continuer la lecture'],

        // ---- L'éditeur
        'house.meta' => ['L’éditeur', 'Description pour Google (meta)', 'text', 'La maison d’édition fondée par l’auteur.'],
        'house.title' => ['L’éditeur', 'Titre de la page', 'line', 'L’éditeur'],
        'house.name' => ['L’éditeur', 'Nom de la maison', 'line', ''],
        'house.intro' => ['L’éditeur', 'Chapeau', 'text', ''],
        'house.body' => ['L’éditeur', 'Présentation', 'rich', ''],
        'house.figures_title' => ['L’éditeur', 'Titre : chiffres', 'line', 'En chiffres'],
        'house.collections_title' => ['L’éditeur', 'Titre : collections', 'line', 'Collections'],
        'house.distinctions_title' => ['L’éditeur', 'Titre : distinctions', 'line', 'Distinctions'],
        'house.cta' => ['L’éditeur', 'Bouton vers le site de la maison', 'line', 'Visiter le site de la maison'],
        'house.url' => ['L’éditeur', 'Adresse du site de la maison', 'line', ''],
        'house.note' => ['L’éditeur', 'Mention : distinction auteur / maison', 'text', 'Le catalogue de la maison réunit de nombreux auteurs : il est distinct de l’œuvre personnelle de l’auteur.'],

        // ---- Actualités
        'news.meta' => ['Actualités', 'Description pour Google (meta)', 'text', 'Parutions, dédicaces, salons et apparitions publiques.'],
        'news.title' => ['Actualités', 'Titre de la page', 'line', 'Actualités'],
        'news.intro' => ['Actualités', 'Chapeau', 'text', ''],
        'news.upcoming' => ['Actualités', 'Titre : à venir', 'line', 'À venir'],
        'news.past' => ['Actualités', 'Titre : passées', 'line', 'Au fil du temps'],
        'news.empty' => ['Actualités', 'Aucune actualité', 'line', 'Aucune actualité pour le moment.'],
        'news.type_parution' => ['Actualités', 'Type : parution', 'line', 'Parution'],
        'news.type_dedicace' => ['Actualités', 'Type : dédicace', 'line', 'Dédicace'],
        'news.type_salon' => ['Actualités', 'Type : salon', 'line', 'Salon'],
        'news.type_apparition' => ['Actualités', 'Type : apparition', 'line', 'Apparition'],
        'news.type_media' => ['Actualités', 'Type : média', 'line', 'Média'],

        // ---- Contact
        'contact.meta' => ['Contact', 'Description pour Google (meta)', 'text', 'Contacter l’auteur : formulaire, WhatsApp, e-mail.'],
        'contact.title' => ['Contact', 'Titre de la page', 'line', 'Contact'],
        'contact.intro' => ['Contact', 'Chapeau', 'text', 'Pour une invitation, un entretien, une commande ou une dédicace.'],
        'contact.form_title' => ['Contact', 'Titre : formulaire', 'line', 'Écrire un message'],
        'contact.name' => ['Contact', 'Champ : nom', 'line', 'Nom'],
        'contact.email' => ['Contact', 'Champ : e-mail', 'line', 'E-mail'],
        'contact.phone' => ['Contact', 'Champ : téléphone', 'line', 'Téléphone (facultatif)'],
        'contact.subject' => ['Contact', 'Champ : objet', 'line', 'Objet'],
        'contact.message' => ['Contact', 'Champ : message', 'line', 'Message'],
        'contact.consent' => ['Contact', 'Case de consentement', 'text', 'J’accepte que ces informations soient utilisées pour répondre à ma demande.'],
        'contact.send' => ['Contact', 'Bouton envoyer', 'line', 'Envoyer'],
        'contact.sent' => ['Contact', 'Message envoyé', 'text', 'Merci, votre message a bien été envoyé.'],
        'contact.error' => ['Contact', 'Erreur de saisie', 'text', 'Merci de vérifier les champs indiqués.'],
        'contact.direct_title' => ['Contact', 'Titre : contact direct', 'line', 'En direct'],
        'contact.whatsapp' => ['Contact', 'Libellé WhatsApp', 'line', 'Écrire sur WhatsApp'],
        'contact.whatsapp_msg' => ['Contact', 'Message WhatsApp pré-rempli (contact)', 'text', 'Bonjour, je vous écris depuis votre site.'],
        'contact.social_title' => ['Contact', 'Titre : réseaux', 'line', 'Réseaux'],

        // ---- Pied de page, cookies, pages légales
        'footer.tagline' => ['Pied de page', 'Phrase du pied de page', 'text', ''],
        'footer.legal' => ['Pied de page', 'Lien mentions légales', 'line', 'Mentions légales'],
        'footer.privacy' => ['Pied de page', 'Lien confidentialité', 'line', 'Confidentialité'],
        'footer.cookies' => ['Pied de page', 'Lien gestion des cookies', 'line', 'Cookies'],
        'footer.links_title' => ['Pied de page', 'Titre : liens', 'line', 'Explorer'],
        'footer.follow_title' => ['Pied de page', 'Titre : réseaux', 'line', 'Suivre'],
        'cookies.text' => ['Cookies', 'Texte du bandeau', 'text', 'Ce site utilise une mesure d’audience anonyme. Les contenus externes (vidéos, audios) ne se chargent qu’avec votre accord.'],
        'cookies.accept' => ['Cookies', 'Bouton accepter', 'line', 'Accepter'],
        'cookies.refuse' => ['Cookies', 'Bouton refuser', 'line', 'Refuser'],
        'legal.title' => ['Pages légales', 'Mentions légales : titre', 'line', 'Mentions légales'],
        'legal.body' => ['Pages légales', 'Mentions légales : contenu', 'rich', ''],
        'privacy.title' => ['Pages légales', 'Confidentialité : titre', 'line', 'Politique de confidentialité'],
        'privacy.body' => ['Pages légales', 'Confidentialité : contenu', 'rich', ''],
        '404.title' => ['Pages légales', 'Page 404 : titre', 'line', 'Cette page s’est égarée entre deux chapitres.'],
        '404.text' => ['Pages légales', 'Page 404 : texte', 'text', 'Le lien est peut-être ancien, ou la page a changé de place.'],
        '404.cta' => ['Pages légales', 'Page 404 : bouton', 'line', 'Revenir à l’accueil'],
    ];
}

/** Récupère un texte dans la langue courante (repli : langue par défaut, puis valeur du registre). */
function t(string $key, array $vars = []): string
{
    static $cache = [];
    $lang = current_lang();
    if (!isset($cache[$lang])) {
        $cache[$lang] = [];
        foreach (rows('SELECT key, value, lang FROM texts WHERE lang IN (?, ?)', [$lang, default_lang()]) as $r) {
            if ($r['lang'] === $lang || !isset($cache[$lang][$r['key']])) {
                if ($r['lang'] === $lang && ($r['value'] ?? '') === '') continue;
                $cache[$lang][$r['key']] = $r['value'];
            }
        }
    }
    $v = $cache[$lang][$key] ?? (text_registry()[$key][3] ?? $key);
    if ($vars) $v = strtr($v, array_combine(array_map(fn($k) => '{' . $k . '}', array_keys($vars)), array_values($vars)));
    return (string) $v;
}

/** Variante échappée pour l'affichage direct. */
function te(string $key, array $vars = []): string { return e(t($key, $vars)); }
