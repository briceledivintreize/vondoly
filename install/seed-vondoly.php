<?php
/**
 * Mise en ligne initiale du site de Sébastien Vondoly à partir du cahier des charges
 * (annexes A et B, sections 4.2, 4.3, 14) et des médias fournis.
 *
 *   php install/seed-vondoly.php [dossier-medias] [--force]
 *
 * Par défaut, les médias sont lus dans install/medias (couvertures/ et photos/).
 *
 * --force : remplace une base existante (une sauvegarde est faite avant).
 * Les comptes créés et leurs mots de passe sont écrits dans data/identifiants-initiaux.txt.
 */
declare(strict_types=1);
if (PHP_SAPI !== 'cli') exit("Script en ligne de commande uniquement.\n");

require dirname(__DIR__) . '/app/bootstrap.php';
require APP . '/install_lib.php';

$args = array_slice($argv, 1);
$force = in_array('--force', $args, true);
$mediaDir = realpath(array_values(array_filter($args, fn($a) => $a !== '--force'))[0] ?? (ROOT . '/install/medias')) ?: '';
if (!is_dir($mediaDir . '/couvertures')) exit("Dossier de médias introuvable : indiquez le dossier contenant « couvertures » et « photos ».\n");

if (db_installed()) {
    if (!$force) exit("Une base existe déjà (data/site.sqlite). Relancez avec --force pour la remplacer.\n");
    $bk = DATA . '/backups/avant-seed-' . date('Ymd-His') . '.sqlite';
    @mkdir(DATA . '/backups', 0775, true);
    copy(DB_FILE, $bk);
    @unlink(DB_FILE); @unlink(DB_FILE . '-wal'); @unlink(DB_FILE . '-shm');
    echo "Ancienne base sauvegardée : $bk\n";
}

$t0 = microtime(true);
echo "Création de la base…\n";
install_base([
    'name' => 'Sébastien Vondoly', 'full_name' => 'Kodzo Adzewoda Vondoly', 'domain' => 'sebastienvondoly.com', 'email' => '',
    'whatsapp' => '22899592629', 'whatsapp_display' => '+228 99 59 26 29', 'profile' => 'ecrivain', 'palette' => 'continents',
    'modules' => ['parcours' => 1, 'reconnaissance' => 1, 'oeuvres' => 1, 'editeur' => 1, 'actualites' => 1, 'contact' => 1, 'bibliotheque3d' => 1],
]);
setting_set('site.nationality', 'Togolaise');

$T = function (string $key, string $value) { q('INSERT OR REPLACE INTO texts(key, lang, value) VALUES(?, ?, ?)', [$key, 'fr', $value]); };

/* --------------------------------------------------------------- Textes */
$T('home.meta', 'Sébastien Vondoly, écrivain, journaliste et éditeur togolais : parcours, distinctions, presse et l’ensemble de ses livres, à commander par WhatsApp.');
$T('home.kicker', 'Écrivain · Journaliste · Éditeur');
$T('home.tagline','Faire entendre la voix du poète, de Lomé au reste du monde.');
$T('home.role1_text', 'Poète, essayiste, biographe et romancier : 23 titres publiés depuis 2007, chez neuf éditeurs de cinq pays.');
$T('home.role2_text', 'Plus de vingt ans de radio et de presse écrite, depuis « La Voix du Poète » sur Radio Lomé en 2003.');
$T('home.role3_text', 'Fondateur, en 2011, des Éditions Continents, qui comptent aujourd’hui plus de 130 ouvrages.');
$T('home.figures_house_value', '130');
$T('footer.tagline', 'Écrivain, journaliste et éditeur togolais. Président du Cénacle, délégué d’Europoésie au Togo.');
$T('parcours.meta', 'Biographie de Sébastien Vondoly (Kodzo Adzewoda Vondoly) : La Voix du Poète, Le Cénacle, Europoésie, Éditions Continents.');
$T('parcours.intro', 'Plus de vingt ans de présence à la radio, dans la presse et dans l’édition.');
$T('parcours.bio', "Sébastien Vondoly, de son nom complet Kodzo Adzewoda Vondoly, est écrivain, journaliste et éditeur togolais. Il se fait connaître à partir de 2003 avec « La Voix du Poète », émission de Radio Lomé, tout en exerçant le journalisme dans la presse écrite.\n\nVers 2006, il devient l’assistant d’Ephrem Seth Dorkenoo aux Éditions de la Rose Bleue, dont il reprend le flambeau après le décès de celui-ci, en 2010. Président du Cénacle depuis 2008, il publie en 2011 son premier recueil, « L’hymne d’un prince sans royaume », préfacé par Robert Dussey, et fonde la même année les Éditions Continents, qui comptent aujourd’hui plus de 130 ouvrages.\n\nDélégué d’Europoésie au Togo depuis 2018, poète, essayiste, biographe et romancier, il a publié 23 titres en 25 éditions et présenté ses livres dans une dizaine de pays d’Afrique et d’Europe.");
$T('parcours.statement', 'Plus de vingt ans au service des mots.');
$T('parcours.timeline_kicker', 'De Radio Lomé aux Éditions Continents');
$T('reco.intro','Distinctions, articles, entretiens et témoignages, avec leur intitulé exact et leur source.');
$T('reco.meta', 'Distinctions, presse et entretiens de Sébastien Vondoly, écrivain et éditeur togolais.');
$T('house.name', 'Éditions Continents');
$T('house.url', 'https://editionscontinents.com');
$T('house.intro', 'La maison d’édition qu’il a fondée à Lomé en 2011 : une facette de son parcours, distincte de son œuvre d’auteur.');
$T('house.body', "Sébastien Vondoly fonde les Éditions Continents à Lomé le 1er juillet 2011 ; la maison commence effectivement ses activités en 2013.\n\nElle publie des auteurs togolais et africains : poésie, romans, essais, biographies et hommages, au fil de collections comme « Scriptorium », « Cris de cœur » ou « Regards ». Son catalogue réunit aujourd’hui plus de 130 ouvrages.\n\nLe catalogue complet de la maison, et sa boutique, se trouvent sur son propre site.");
$T('house.meta', 'Les Éditions Continents, maison d’édition fondée à Lomé en 2011 par Sébastien Vondoly.');
$T('books.meta', 'Les 25 éditions des livres de Sébastien Vondoly : poésie, essais, romans, biographies, hommages. Commande par WhatsApp.');
$T('contact.intro', 'Pour une invitation, un entretien, une commande ou une dédicace : écrivez-lui directement.');

/* --------------------------------------------------- Réseaux sociaux */
q("UPDATE social_links SET url = 'https://x.com/SVondoly', label = '@SVondoly' WHERE network = 'x'");

/* ---------------------------------------------------- Listes (éditeurs) */
$pub = [];
foreach ([
    ['Éditions Continents', 'Lomé', 'https://editionscontinents.com'], ['L’Harmattan', 'Paris', 'https://www.editions-harmattan.fr'],
    ['Éditions Awoudy', 'Lomé', null], ['Éditions de la Rose Bleue', 'Lomé', null], ['Éditions GNK', 'Abidjan', null],
    ['Editura Pim', 'Iași', null], ['Éditions Stellamaris', 'Paris', null], ['Éditions Sawa', 'Bamako', null],
    ['Éditions Panafrika/Silex/Nouvelles du Sud', 'Ivry-sur-Seine (France)', null],
] as $i => [$n, $c, $u]) $pub[$n] = insert('publishers', ['name' => $n, 'slug' => slugify($n), 'city' => $c, 'url' => $u, 'sort' => $i + 1]);
$genre = [];
foreach (rows('SELECT id, name FROM genres') as $g) $genre[$g['name']] = (int) $g['id'];

/* ------------------------------------------------------------- Images */
$cov = $mediaDir . '/couvertures/';
$img = function (?string $file, array $opts = []) use ($cov): ?int {
    if (!$file) return null;
    $path = str_contains($file, '/') ? $file : $cov . $file;
    if (!is_file($path)) { echo "  ! image absente : $file\n"; return null; }
    return image_process($path, $opts + ['name' => pathinfo($path, PATHINFO_FILENAME)]);
};

/* -------------------------------------------------------------- Livres */
// [n°, titre, sous-titre, genre (filtre), genre exact, année, édition, éditeur, lieu, collection, préface, postface, pages, isbn, mention, collectif, recto, verso, note, visible]
$books = [
 [1, 'Les Étoiles d’Outre-Ciel', null, 'Anthologie', 'Anthologie de poèmes', 2007, 1, 'Éditions de la Rose Bleue', 'Lomé', null, 'Hélène Estradère', null, null, null, 'Ouvrage collectif auquel il a contribué', 1, null, null, null, 1,
  "Paru en novembre 2007 aux Éditions de la Rose Bleue, à Lomé, ce recueil collectif réunit trente poètes togolais. Né dans le prolongement de la première Rencontre Internationale des Créateurs en Poésie (RICEP), l’ouvrage est initié par Ephrem Seth Dorkenoo et préfacé par Hélène Estradère. Il constitue un témoignage marquant de la vitalité de la poésie togolaise des années 2000."],
 [2, 'L’hymne d’un prince sans royaume', null, 'Poésie', 'Poésie', 2011, 1, 'Éditions Awoudy', 'Lomé', null, 'Pr Robert Dussey', null, '120', null, null, 0, '02_l-hymne-d-un-prince-sans-royaume_2011_awoudy_recto_BASSE-QUALITE.jpg', null,
  "Genre non précisé dans la liste du client (« — ») : « Poésie » déduit du résumé, à confirmer. Couverture en basse définition à remplacer.", 1,
  "Premier recueil individuel de Kodzo Adzewoda Vondoly, publié début 2011 aux Éditions Awoudy, à Lomé, et préfacé par le professeur Robert Dussey. Ce recueil de poésie de 120 pages se présente comme un plaidoyer dénonçant les injustices quotidiennes subies par les travailleurs. Il a rencontré un écho favorable auprès du public togolais, permettant à l’auteur de s’affirmer sur la scène poétique nationale."],
 [3, 'Le murmure des martyrs', null, 'Poésie', null, 2012, 1, 'Éditions Continents', 'Lomé', 'Scriptorium', 'Magloire K. Kuakuvi', 'Zeus Komi Aziadouvo', null, null, null, 0, '03_le-murmure-des-martyrs_2012_continents_recto.jpg', '03_le-murmure-des-martyrs_2012_continents_verso.jpg', null, 1,
  "Recueil de poèmes engagé de Kodzo Adzewoda Vondoly, publié à Lomé en 2012 aux Éditions Continents (collection Scriptorium), préfacé par le philosophe Magloire K. Kuakuvi et postfacé par le journaliste Zeus Komi Aziadouvo.\n\nConçu à l’origine en mémoire de l’historien et homme politique togolais Atsoutsè Agbobli, l’ouvrage porte la voix des peuples opprimés et dénonce les violences politiques ainsi que les atrocités liées à la colonisation et aux régimes autoritaires en Afrique. Il célèbre plus largement les martyrs et pères des indépendances africaines, tels que Sylvanus Olympio, Tavio Amorin, Patrice Lumumba, Kwame Nkrumah, Thomas Sankara ou encore Norbert Zongo."],
 [4, 'Les secrets de Paul Ahyi', null, 'Poésie', null, 2013, 1, 'Éditions Continents', 'Lomé', 'Scriptorium', 'Joseph Kokou Koffigoh', null, null, '978-2-918706-12-0', null, 0, '04_les-secrets-de-paul-ahyi_2013_continents_recto.jpg', '04_les-secrets-de-paul-ahyi_2013_continents_verso.jpg', null, 1,
  "Recueil de poésie-hommage de Kodzo Adzewoda Vondoly, publié en 2013 à Lomé aux Éditions Continents (collection Scriptorium), préfacé par l’ancien Premier ministre et écrivain Joseph Kokou Koffigoh.\n\nL’ouvrage célèbre la vie et l’héritage de Paul Ahyi (1930-2010), figure majeure des arts plastiques au Togo et concepteur du drapeau togolais : le poète y retrace son parcours, depuis sa formation aux Beaux-Arts jusqu’à son enseignement au lycée de Tokoin et à l’EAMAU, ainsi que ses œuvres monumentales exposées au Togo, au Vatican et à travers le monde. L’auteur y intègre aussi une réflexion engagée sur l’Afrique et les défis démocratiques du continent."],
 [5, 'Quand naîtra un homme comme toi…', 'Hommage à Nelson Mandela', 'Poésie', null, 2014, 1, 'Éditions Continents', 'Lomé', null, 'Steve Bodjona', null, '106', null, 'Coécrit avec David Emmanuel Accoh', 0, '05_quand-naitra-un-homme-comme-toi_2014_continents_recto_BASSE-QUALITE.jpg', null, 'Couverture en basse définition (150 px) à remplacer.', 1,
  "Publié en novembre 2014 aux Éditions Continents, ce recueil de poésie de 106 pages est coécrit par Kodzo Adzewoda Vondoly et David Emmanuel Accoh, avec une préface de Steve Bodjona. L’ouvrage rend hommage à Nelson Mandela à l’approche du premier anniversaire de sa disparition, célébrant à travers les vers la lutte contre l’apartheid et l’héritage de l’homme d’État."],
 [6, 'Le journalisme au Togo : contraintes et réalités du terrain', null, 'Essai', null, 2015, 1, 'Éditions Continents', 'Lomé', null, 'Gilles Bocco', null, '233', '978-2-918706-06-9', null, 0, '06_le-journalisme-au-togo_2015_continents_recto_BASSE-QUALITE.jpg', null, 'Couverture en basse définition (150 px) à remplacer.', 1,
  "Fruit d’une enquête entamée dès fin 2009 auprès de nombreux confrères au Togo et à l’étranger, cet essai de Kodzo Adzewoda Vondoly, préfacé par Gilles Bocco et publié en février 2015 aux Éditions Continents (233 pages, 17 chapitres), dresse un état des lieux sans concession de la presse privée togolaise.\n\nIl y aborde la précarité économique des professionnels des médias, identifiée comme source principale de nombreux dérapages déontologiques ; les dérives de la corporation, notamment la transformation de certains acteurs en « mercenaires de la plume » ; et l’insécurité des journalistes, régulièrement exposés aux agressions et à diverses formes de répression."],
 [7, 'Le temps d’une vie', null, null, null, 2018, 1, 'Éditions GNK', 'Abidjan', null, null, null, null, null, null, 0, null, null, 'Couverture et résumé à fournir ; genre non précisé.', 1, null],
 [8, 'Anthologie Mondiale (World Anthology)', null, 'Anthologie', 'Collectif, poésie', 2019, 1, 'Editura Pim', 'Iași', null, null, null, '119-126', null, 'Ouvrage collectif, pages 119-126', 1, null, null, 'Couverture et résumé à fournir.', 1, null],
 [9, 'Carnet d’un voyage à La Haye', null, 'Poésie', null, 2020, 1, 'L’Harmattan', 'Paris', null, null, null, null, null, null, 0, '09_carnet-d-un-voyage-a-la-haye_2020_harmattan_recto.jpg', '09_carnet-d-un-voyage-a-la-haye_2020_harmattan_verso.jpg', null, 1,
  "Recueil de poésie de Sébastien Vondoly, publié en novembre 2020 aux Éditions L’Harmattan, qui explore la crise post-électorale ivoirienne et le transfert de Laurent Gbagbo à la Cour pénale internationale."],
 [10, 'La Femme de Dieu', null, 'Poésie', null, 2021, 1, 'L’Harmattan', 'Paris', 'L’Afrique qui se bat', 'Léontine Syra Séhoué', 'Roger Gbégnonvi', '74', '978-2-343-22369-8', null, 0, '10_la-femme-de-dieu_2021_harmattan_recto.jpg', '10_la-femme-de-dieu_2021_harmattan_verso.jpg', null, 1,
  "Recueil de poésie de 74 pages de Sébastien Vondoly, publié en février 2021 aux Éditions L’Harmattan (collection « L’Afrique qui se bat »), préfacé par Léontine Syra Séhoué et postfacé par Roger Gbégnonvi.\n\nL’ouvrage est un hommage poétique de l’auteur à sa mère, ainsi qu’un hymne dédié aux femmes au foyer engagées pour leur famille et leurs enfants. Il a été finaliste du prix littéraire Komlan Messan Nubukpo de la poésie, à l’Université de Lomé, en 2022."],
 [11, 'Les complaintes de la Terre', 'Poèmes en hommage à Wangari Muta Maathai, Prix Nobel de la Paix 2004', 'Poésie', null, 2021, 1, 'Éditions Stellamaris', 'Paris', null, null, null, '60', '978-2-36868-732-1', null, 0, '11_les-complaintes-de-la-terre_2021_stellamaris_recto_BASSE-QUALITE.jpg', '11_les-complaintes-de-la-terre_2021_stellamaris_verso_BASSE-QUALITE.jpg', 'Couvertures en basse définition (225 px) à remplacer.', 1,
  "Recueil de poésie de Sébastien Vondoly, « Poèmes en hommage à Wangari Muta Maathai, Prix Nobel de la Paix 2004 », publié le 20 septembre 2021 aux Éditions Stellamaris (Paris), 60 pages. L’ouvrage rend un hommage poétique à la militante écologiste et féministe kényane Wangari Maathai et à son combat pour la protection de l’environnement."],
 [12, 'La violence des mots', null, 'Poésie', null, 2021, 1, 'Éditions Sawa', 'Bamako', null, null, null, null, null, null, 0, null, null, 'Couverture et résumé à fournir.', 1, null],
 [13, 'Me Yawovi Agboyibo, dans l’antre du Bélier Noir', null, 'Récit', null, 2021, 1, 'L’Harmattan', 'Paris', null, 'Me Georges Messan Bouaka', null, '122', null, null, 0, '13_me-yawovi-agboyibo_2021_harmattan_recto.jpg', '13_me-yawovi-agboyibo_2021_harmattan_verso.jpg', null, 1,
  "Récit biographique de Sébastien Vondoly, publié en 2021 aux éditions L’Harmattan (122 pages), préfacé par Me Georges Messan Bouaka. L’ouvrage retrace la vie et l’engagement sociopolitique de Me Yawovi Agboyibo (1943-2020), figure majeure de la vie publique togolaise et ancien Premier ministre, mêlant parcours institutionnel et facettes plus intimes de celui que l’on surnommait le « Bélier Noir de Kouvé »."],
 [14, 'Le crépuscule des atouts falsifiés', null, 'Poésie', null, 2021, 1, 'Éditions Panafrika/Silex/Nouvelles du Sud', 'Ivry-sur-Seine (France)', null, null, null, null, null, null, 0, null, null, 'Couverture et résumé à fournir.', 1, null],
 [15, 'Togo : La liberté d’expression à l’épreuve des réseaux sociaux', null, 'Essai', null, 2022, 1, 'L’Harmattan', 'Paris', 'L’Afrique qui se bat', 'Noël Kokou Tadégnon', 'Yves Galley', null, '978-2-14-026241-8 (papier) / 978-2-14-026242-5 (numérique)', null, 0, null, null, 'Couverture à fournir.', 1,
  "Essai de Sébastien Vondoly, publié le 3 mai 2022 aux éditions L’Harmattan (collection « L’Afrique qui se bat »), préfacé par Noël Kokou Tadégnon et postfacé par Yves Galley.\n\nL’ouvrage analyse l’usage des plateformes numériques dans la société togolaise : le cadre légal de la liberté d’expression et ses limites, avec les risques juridiques encourus en cas d’abus, et les dérives numériques, lorsque les réseaux sont détournés pour insulter, diffamer ou porter atteinte à la vie d’autrui."],
 [16, 'Les secrets de Paul Ahyi', null, 'Poésie', null, 2023, 2, 'L’Harmattan', 'Paris', 'L’Afrique qui se bat', null, 'Maximilien Amégée', '90', '978-2-14-032808-4', null, 0, '16_les-secrets-de-paul-ahyi_2023_harmattan-2e-ed_recto.jpg', '16_les-secrets-de-paul-ahyi_2023_harmattan-2e-ed_verso.jpg', null, 1,
  "Réédition, le 20 février 2023 aux Éditions L’Harmattan (collection « L’Afrique qui se bat »), du recueil de poésie-hommage de Sébastien Vondoly consacré à Paul Ahyi (1930-2010), figure majeure des arts plastiques au Togo et créateur du drapeau togolais.\n\nLes poèmes retracent l’itinéraire de l’artiste, depuis sa formation à l’École nationale supérieure des Beaux-Arts de Paris jusqu’à ses années d’enseignement au lycée de Tokoin et à l’EAMAU, à Lomé, en évoquant ses œuvres monumentales laissées au Togo, au Vatican et à travers le monde. L’ouvrage consacre aussi une partie de ses textes aux réalités sociopolitiques du continent africain. Cette édition, postfacée par Maximilien Amégée, compte 90 pages."],
 [17, 'La Femme de Dieu', null, 'Poésie', null, 2023, 2, 'L’Harmattan', 'Paris', null, null, null, null, null, null, 0, null, null, 'Couverture à fournir.', 1,
  "Réédition, en 2023 aux Éditions L’Harmattan, du recueil de poésie de Sébastien Vondoly rendant hommage à la figure de la mère et aux femmes au foyer engagées pour leur famille et leurs enfants."],
 [18, 'Victoire Sidémého Tomégah-Dogbé, première femme cheffe de gouvernement au Togo', 'Pour l’histoire et la postérité', 'Essai', null, 2023, 1, 'Éditions Continents', 'Lomé', 'Regards', null, null, '152', '978-2-918706-77-9', null, 0, '18_victoire-tomegah-dogbe_2023_continents_recto_BASSE-QUALITE.jpg', '18_victoire-tomegah-dogbe_2023_continents_verso_BASSE-QUALITE.jpg', 'Couvertures en basse définition (150 px) à remplacer.', 1,
  "Essai de 152 pages de Sébastien Vondoly, publié en 2023 aux Éditions Continents (collection « Regards »). L’ouvrage retrace l’ascension et l’engagement de Victoire Tomégah-Dogbé, nommée Première ministre en septembre 2020 et devenue la première femme à occuper ce poste depuis l’indépendance du Togo en 1960.\n\nÀ travers cette figure de l’État togolais, l’auteur rend hommage aux femmes togolaises et africaines, en répertoriant notamment les autres femmes ayant accédé à la fonction de cheffe de gouvernement sur le continent."],
 [19, 'La sixième décennie', 'Anthologie de la poésie togolaise', 'Anthologie', 'Anthologie', 2024, 1, 'Éditions Awoudy', 'Lomé', null, null, null, '264', '9782373164886', 'Ouvrage collectif : Sébastien Vondoly figure parmi les contributeurs', 1, '19_la-sixieme-decennie_2024_awoudy_recto_BASSE-QUALITE.jpg', '19_la-sixieme-decennie_2024_awoudy_verso_BASSE-QUALITE.jpg',
  "Point 14-6 : la couverture indique une direction par Ayi Renaud Dossavi, Patron Henekou et Kudavi Nomanyo. Préciser le rôle exact de l’auteur (contributeur ?) avant d’afficher la fiche.", 0,
  "Ouvrage collectif paru en mai 2024 aux Éditions Awoudy, dirigé par les écrivains et poètes togolais Ayi Renaud Dossavi, Patron Henekou et Kudavi Nomanyo. Porté par l’association PEN-Togo, il dresse le panorama de la création poétique au Togo entre 2010 et 2020 et rassemble plus d’une centaine de textes de 44 poètes du Togo et de la diaspora. Le livre, de 264 pages, a été dédicacé à Lomé le 17 mai 2024. Sébastien Vondoly figure parmi les contributeurs."],
 [20, 'Senghor ou rien', null, 'Roman', null, 2025, 1, 'Éditions Continents', 'Lomé', 'Cris de cœur', 'Martin Gbénouga', null, '170', '978-2-918707-26-4', null, 0, '20_senghor-ou-rien_2025_continents_recto_BASSE-QUALITE.jpg', '20_senghor-ou-rien_2025_continents_verso_BASSE-QUALITE.jpg', 'Couvertures à remplacer par les fichiers d’origine (Éditions Continents).', 1,
  "Roman de Sébastien Vondoly, publié en 2025 aux Éditions Continents (collection « Cris de cœur »), préfacé par Martin Gbénouga, 170 pages.\n\nLe récit suit Kévin, un jeune élève passionné par les écrits de Léopold Sédar Senghor, au point de mémoriser une grande partie de ses poèmes. Plutôt qu’une biographie de l’ancien président sénégalais, l’ouvrage utilise la figure de Senghor comme symbole d’exigence intellectuelle pour inciter la jeunesse, à l’ère du tout-numérique, à renouer avec les livres."],
 [21, 'Noël Kokou Tadégnon, le journaliste tout-terrain', 'Un parcours, un exemple', 'Biographie', null, 2025, 1, 'Éditions Continents', 'Lomé', 'Regard', null, null, '145', null, null, 0, null, null, 'Couverture à fournir.', 1,
  "Biographie de Sébastien Vondoly, publiée le 4 avril 2025 aux Éditions Continents (collection « Regard »), 145 pages.\n\nLe livre retrace la vie et la carrière de Noël Kokou Tadégnon, figure emblématique de la presse togolaise : ses origines à Lomé, ses débuts dans le journalisme dès 1993, son rôle de correspondant pour des agences internationales (Reuters, Deutsche Welle, AfricaNews), ses spécialisations en web-journalisme, fact-checking et OSINT, ainsi que ses engagements professionnels, dont la création de la plateforme Mediatogo. Le récit mêle narration biographique, anecdotes sur les risques du métier, une interview du principal intéressé et des témoignages croisés de ses confrères."],
 [22, 'Edem Kodjo, les secrets d’une grandeur', 'Hommages', 'Hommages', 'Hommages-témoignages', 2025, 1, 'Éditions Continents', 'Lomé', null, null, null, '328', null, 'Ouvrage collectif dirigé par Sébastien Vondoly', 0, '22_edem-kodjo-les-secrets-d-une-grandeur_2025_continents_recto_BASSE-QUALITE.jpg', null, 'Couverture en basse définition à remplacer.', 1,
  "Ouvrage collectif dirigé par Sébastien Vondoly, publié en 2025 aux Éditions Continents (328 pages). Ce recueil réunit des témoignages sur la vie et l’héritage d’Edem Kodjo, ancien Premier ministre togolais, mettant en avant son humanisme, sa recherche de paix par le pardon et son engagement panafricain, à travers les contributions de diverses personnalités, dont le professeur Robert Dussey."],
 [23, 'Je préfère la mort à ma mère', null, 'Roman', null, 2025, 1, 'Éditions Continents', 'Lomé', 'Cris de cœur', 'Pr Togoata Apédo-Amah', null, null, null, null, 0, '23_je-prefere-la-mort-a-ma-mere_2025_continents_recto_BASSE-QUALITE.jpg', '23_je-prefere-la-mort-a-ma-mere_2025_continents_verso_BASSE-QUALITE.jpg', 'Couvertures en basse définition (150 px) à remplacer.', 1,
  "Roman de Sébastien Vondoly, paru en 2025 aux Éditions Continents (collection « Cris de cœur »), préfacé par le professeur Togoata Apédo-Amah.\n\nLe récit suit Yann, un jeune homme promis à un bel avenir qui tombe gravement malade juste après avoir célébré l’obtention de sa Green Card pour les États-Unis. Face à l’échec des soins et des prières, un féticheur lui impose un remède impliquant un choix impensable vis-à-vis de sa propre mère, auquel Yann et son père opposent un refus catégorique.\n\nL’œuvre explore les ravages des pratiques occultes, les accusations de sorcellerie au sein de la cellule familiale et le choc entre croyances animistes et foi chrétienne, tout en mettant en avant la résilience et la force des liens familiaux face à l’adversité."],
 [24, 'Un ami n’est pas frère', null, 'Roman', null, 2026, 1, 'Éditions Continents', 'Lomé', null, null, null, null, null, null, 0, '24_un-ami-n-est-pas-frere_2026_continents_recto.jpg', null, 'Résumé à rédiger avec l’auteur.', 1, null],
 [25, 'Kangni Alem, parcours, œuvres et traces', null, 'Hommages', null, 2026, 1, 'Éditions Continents', 'Lomé', null, null, null, null, null, 'Recueil d’hommages et d’études critiques dirigé par Sébastien Vondoly', 0, null, null, 'Couverture à fournir.', 1,
  "Recueil d’hommages et d’études critiques dirigé par Sébastien Vondoly, publié en octobre 2026 aux Éditions Continents, célébrant la carrière littéraire, théâtrale et universitaire de Kangni Alem, qui a fêté ses 60 ans en 2026. L’ouvrage a été lancé en avant-première fin octobre 2026 lors de la Fête du livre à l’Institut français du Togo.\n\nNé à Lomé en 1966, Kangni Alem est une figure majeure de la littérature francophone contemporaine : diplômé en sémiologie théâtrale et docteur en littérature comparée, fondateur de l’Atelier Théâtre de Lomé en 1989, il a enseigné aux États-Unis, en France et à l’Université de Lomé. Auteur d’une vingtaine d’ouvrages, il est notamment connu pour sa pièce « Chemins de croix » (Prix Tchicaya U’Tamsi 1990) et son roman « Cola Cola jazz » (Grand Prix littéraire d’Afrique noire 2003)."],
];

$ids = [];
foreach ($books as $b) {
    [$n, $title, $sub, $g, $gl, $year, $ed, $pubName, $place, $coll, $pre, $post, $pages, $isbn, $mention, $collective, $recto, $verso, $note, $visible, $summary] = $b;
    echo sprintf("  Livre %02d · %s\n", $n, $title);
    $base = slugify($title) . ($ed > 1 ? '-' . $ed . 'e-edition' : '');
    $ids[$n] = insert('books', [
        'slug' => $base, 'title' => $title, 'subtitle' => $sub, 'genre_id' => $g ? ($genre[$g] ?? null) : null, 'genre_label' => $gl,
        'year' => $year, 'edition_num' => $ed, 'publisher_id' => $pub[$pubName] ?? null, 'place' => $place, 'collection' => $coll,
        'preface' => $pre, 'postface' => $post, 'pages' => $pages, 'isbn' => $isbn, 'summary' => $summary, 'special_note' => $mention,
        'is_collective' => $collective, 'cover_id' => $img($recto), 'back_id' => $img($verso),
        'featured' => $n === 24 ? 1 : 0, 'visible' => $visible, 'sort' => $n, 'note' => $note,
        'publisher_link' => null,
    ]);
}
// Deux éditions d'un même livre : liens réciproques « Autre édition »
foreach ([[4, 16], [10, 17]] as [$a, $b]) {
    q('UPDATE books SET other_edition_id = ? WHERE id = ?', [$ids[$b], $ids[$a]]);
    q('UPDATE books SET other_edition_id = ? WHERE id = ?', [$ids[$a], $ids[$b]]);
}

/* ------------------------------------------------------------- Photos */
echo "Portrait…\n";
$portrait = $mediaDir . '/photos/portrait-01_vondoly.jpg';
$pid = $img($portrait, ['ratio' => '4:5', 'focus' => 'top', 'alt' => 'Portrait de Sébastien Vondoly, souriant, en chemise à motifs noirs et blancs', 'widths' => [480, 800, 1145]]);
q("UPDATE photos SET media_id = ? WHERE slot = 'home_portrait'", [$pid]);
$hd = $img($portrait, ['alt' => 'Sébastien Vondoly, portrait haute définition', 'widths' => [600, 1145]]);
q("UPDATE photos SET media_id = ? WHERE slot = 'presskit'", [$hd]);
q("UPDATE photos SET note = ? WHERE slot = 'parcours_portrait'", ['Photo différente de celle de l’accueil à fournir par l’auteur.']);
foreach ([1, 2, 3] as $i) insert('photos', ['slot' => 'parcours', 'media_id' => null, 'sort' => $i, 'note' => $i === 1 ? 'Photos de parcours (salons, dédicaces, rencontres, archives) à fournir, avec légende et année.' : null]);
foreach ([1, 2] as $i) insert('photos', ['slot' => 'house', 'media_id' => null, 'sort' => $i]);

/* ---------------------------------------------------------------- Frise */
foreach ([
    ['2003', 'Premières émissions de « La Voix du Poète » sur Radio Lomé.', 1, null],
    ['Vers 2006', 'Devient l’assistant d’Ephrem Seth Dorkenoo aux Éditions de la Rose Bleue.', 0, 'Point 14-11 : date déduite de la quatrième de couverture de « Senghor ou rien », à confirmer avant d’afficher.'],
    ['2007', 'Participation à l’anthologie « Les Étoiles d’Outre-Ciel » (Éditions de la Rose Bleue).', 1, null],
    ['2008', 'Présidence du Cénacle (Association de la Nouvelle Génération d’Écrivains Africains).', 1, null],
    ['2010', 'Prix Sanofi Pasteur du meilleur article de presse sur la grippe aviaire.', 0, 'Point 14-2 : mention trouvée uniquement sur la quatrième de couverture de « La Femme de Dieu ». À confirmer par l’auteur ; ne pas publier sans confirmation.'],
    ['2011', 'Premier recueil, « L’hymne d’un prince sans royaume », préfacé par le Pr Robert Dussey. Fondation des Éditions Continents (1er juillet).', 1, null],
    ['2013', 'Début effectif des activités des Éditions Continents.', 1, null],
    ['2018', 'Délégué de l’association européenne Europoésie au Togo (août).', 1, null],
    ['2020-2021', 'Publications chez L’Harmattan (Paris), Stellamaris (Paris) et Sawa (Bamako).', 1, null],
    ['2022', '« La Femme de Dieu » finaliste du Prix littéraire Komlan Messan Nubukpo de la poésie.', 1, null],
    ['2023', 'Nommé aux Togo Top Impact, catégorie « Meilleur acteur culturel de l’année ».', 1, 'Point 14-4 : nomination à confirmer par l’auteur ; ne jamais écrire « lauréat » sans preuve.'],
    ['2025-2026', 'Série récente de parutions aux Éditions Continents.', 1, null],
] as $i => [$y, $txt, $vis, $note]) insert('timeline', ['year_label' => $y, 'text' => $txt, 'visible' => $vis, 'note' => $note, 'sort' => $i + 1]);

insert('associations', ['name' => 'Le Cénacle', 'role' => 'Président', 'since' => 'Depuis 2008', 'text' => 'Association de la Nouvelle Génération d’Écrivains Africains.', 'sort' => 1]);
insert('associations', ['name' => 'Europoésie', 'role' => 'Délégué au Togo', 'since' => 'Depuis août 2018', 'text' => 'Association européenne de poésie.', 'sort' => 2]);

/* -------------------------------------------------------- Reconnaissance */
insert('distinctions', ['scope' => 'personal', 'status' => 'Finaliste', 'label' => 'Prix littéraire Komlan Messan Nubukpo de la poésie (Université de Lomé), pour « La Femme de Dieu »', 'year' => '2022', 'source' => 'Catalogue des Éditions L’Harmattan', 'sort' => 1]);
insert('distinctions', ['scope' => 'personal', 'status' => 'Nommé', 'label' => 'Togo Top Impact 2023, catégorie « Meilleur acteur culturel de l’année »', 'year' => '2023', 'source' => 'Actu Togo, 15 décembre 2023', 'source_url' => 'https://actu-togo.tg/2023/12/15/trashed-7trashed/', 'sort' => 2, 'note' => 'Point 14-4 : la liste ne dit pas qui a gagné. Conserver « Nommé ».']);
insert('distinctions', ['scope' => 'personal', 'status' => 'Prix', 'label' => 'Prix Sanofi Pasteur du meilleur article de presse sur la grippe aviaire', 'year' => '2010', 'source' => 'Quatrième de couverture de « La Femme de Dieu »', 'visible' => 0, 'sort' => 3, 'note' => 'Point 14-2 : à confirmer par l’auteur ; ne pas publier sans confirmation.']);
insert('distinctions', ['scope' => 'house', 'status' => 'Mention', 'label' => 'Mention de l’édition africaine', 'year' => '2022', 'source' => 'ADELF (Association des écrivains de langue française)', 'sort' => 4, 'note' => 'Point 14-5 : intitulé exact de l’ADELF repris (la maison écrit « Prix »). Vérifier avant mise en production.']);

foreach ([
    ['Icilome', '« Senghor ou rien », le pari de Sébastien Vondoly pour réconcilier la jeunesse avec les livres', 'Juillet 2026', '2026-07-01', 'https://icilome.com/2026/07/togo-litterature-senghor-ou-rien-le-pari-de-sebastien-vondoly-pour-reconcilier-la-jeunesse-avec-les-livres/'],
    ['Le Nouveau Reporter', '« Je préfère la mort à ma mère », le roman choc de Sébastien Vondoly', '2025', '2025-01-01', 'https://lenouveaureporter.com/je-prefere-la-mort-a-ma-mere-le-roman-choc-de-sebastien-vondoly-aux-editions-continents/'],
    ['Actu Togo', 'Nommés Togo Top Impact 2023, meilleur acteur culturel', '15 décembre 2023', '2023-12-15', 'https://actu-togo.tg/2023/12/15/trashed-7trashed/'],
    ['Africultures', 'Coup de projecteur sur une maison d’édition togolaise à la vue large : Continents', 'Début 2023', '2023-01-15', 'https://africultures.com/coup-de-projecteur-sur-une-maison-a-la-vue-large-continents/'],
    ['Icilome', 'Aux Éditions Continents, nous avons publié entre 25 et 30 ouvrages par an', '19 juin 2022', '2022-06-19', 'https://icilome.com/2022/06/togo-sebastien-vondoly-aux-editions-continents-nous-avons-publie-entre-25-et-35-ouvrages-par-an/'],
] as $i => [$m, $title, $dl, $ds, $u]) insert('press', ['media_name' => $m, 'title' => $title, 'date_label' => $dl, 'date_sort' => $ds, 'url' => $u, 'sort' => $i + 1]);

// Témoignages : noms cités dans le cahier des charges, citations exactes à fournir (masqués d'ici là)
foreach ([
    ['Joseph Kokou Koffigoh', 'Ancien Premier ministre, écrivain', 'Les secrets de Paul Ahyi'],
    ['Maximilien Amégée', 'Postfacier', 'Les secrets de Paul Ahyi (2e édition)'],
    ['Martin Dossou Gbénouga', 'Préfacier', 'Senghor ou rien'],
    ['Ayih Ayitey', null, null],
] as $i => [$n, $r, $bk]) insert('testimonials', ['quote' => '[Citation exacte à saisir]', 'name' => $n, 'role' => $r, 'book' => $bk, 'visible' => 0, 'sort' => $i + 1, 'note' => 'Citation exacte et source à fournir par l’auteur avant publication.']);

/* --------------------------------------------------------------- Maison */
foreach ([['130+', 'ouvrages au catalogue'], ['2011', 'année de fondation (1er juillet)'], ['2013', 'début effectif des activités']] as $i => [$v, $l]) insert('house_figures', ['value' => $v, 'label' => $l, 'sort' => $i + 1]);
foreach (['Scriptorium', 'Cris de cœur', 'Regards'] as $i => $c) insert('house_collections', ['name' => $c, 'sort' => $i + 1]);

/* ------------------------------------------------------------ Actualités */
foreach ([
    ['parution', 'Lancement de « Kangni Alem, parcours, œuvres et traces »', '2026-10-31', 'Institut français du Togo, Lomé', 'Le recueil d’hommages et d’études critiques dirigé par Sébastien Vondoly a été lancé en avant-première lors de la Fête du livre.', 'kangni-alem-parcours-oeuvres-et-traces', 'Date exacte du lancement (« fin octobre 2026 ») à confirmer.'],
    ['parution', 'Parution de « Noël Kokou Tadégnon, le journaliste tout-terrain »', '2025-04-04', 'Éditions Continents, Lomé', 'Une biographie consacrée à une figure emblématique de la presse togolaise.', 'noel-kokou-tadegnon-le-journaliste-tout-terrain', null],
    ['parution', 'Réédition des « Secrets de Paul Ahyi » chez L’Harmattan', '2023-02-20', 'L’Harmattan, Paris', 'Le recueil-hommage au créateur du drapeau togolais reparaît en 2e édition, postfacée par Maximilien Amégée.', 'les-secrets-de-paul-ahyi-2e-edition', null],
    ['parution', 'Parution de « Togo : La liberté d’expression à l’épreuve des réseaux sociaux »', '2022-05-03', 'L’Harmattan, Paris', 'Un essai sur le cadre légal de la liberté d’expression et les dérives des plateformes numériques au Togo.', 'togo-la-liberte-d-expression-a-l-epreuve-des-reseaux-sociaux', null],
    ['parution', 'Parution des « Complaintes de la Terre »', '2021-09-20', 'Éditions Stellamaris, Paris', 'Des poèmes en hommage à Wangari Muta Maathai, Prix Nobel de la Paix 2004.', 'les-complaintes-de-la-terre', null],
] as $i => [$type, $title, $date, $place, $excerpt, $bookSlug, $note]) {
    $link = val('SELECT slug FROM books WHERE slug = ?', [$bookSlug]);
    insert('news', ['slug' => slugify($title), 'type' => $type, 'title' => $title, 'date' => $date, 'place' => $place, 'excerpt' => $excerpt,
        'body' => $link ? "[Voir la fiche du livre](/oeuvres/$link)" : null, 'visible' => 1, 'sort' => $i + 1, 'note' => $note]);
}

/* -------------------------------------------------------------- Comptes */
$pw = fn() => rtrim(strtr(base64_encode(random_bytes(12)), '+/', 'kM'), '=');
$adminPw = $pw(); $editorPw = $pw();
install_user('admin@sebastienvondoly.com', 'Administrateur DTC', $adminPw, 'admin');
install_user('auteur@sebastienvondoly.com', 'Sébastien Vondoly', $editorPw, 'editor');
file_put_contents(DATA . '/identifiants-initiaux.txt',
    "Identifiants initiaux de l’administration (" . date('d/m/Y H:i') . ")\nÀ changer dès la première connexion (menu « Mon compte »), puis supprimer ce fichier.\n\n" .
    "Administrateur        : admin@sebastienvondoly.com / $adminPw\n" .
    "Éditeur de contenu    : auteur@sebastienvondoly.com / $editorPw\n");
@chmod(DATA . '/identifiants-initiaux.txt', 0600);

content_touched();
printf("Terminé en %.1f s. Identifiants : data/identifiants-initiaux.txt\n", microtime(true) - $t0);
