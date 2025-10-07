=== Accessible WP Toolkit ===
Contributors: your-name
Tags: accessibility, a11y, wcag, aria
Requires at least: 6.0
Tested up to: 6.x
Requires PHP: 7.4
Stable tag: 0.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Boîte à outils pour auditer rapidement quelques points d’accessibilité clés depuis l’admin WordPress.

== Description ==
Accessible WP Toolkit ajoute une page d’outils dans l’administration :

* **Contraste des couleurs** : calcul du ratio WCAG, indication des niveaux AA/AAA pour texte normal/large et composants UI.
* **Audit clavier** : analyse d’un extrait HTML pour repérer liens sans destination, tabindex positifs ou éléments sans nom accessible.
* **Repères ARIA** : vérification de la présence des landmarks structurels (banner, navigation, main, complementary, contentinfo).
* **Sous-titres & médias** : rappel des vidéos sans piste de sous-titres ou des audios sans transcription/description.

Chaque module fonctionne sans recharger la page complète de WordPress et propose des recommandations prêtes à l’emploi.

== Installation ==
1. Copier le dossier `accessible-wp-toolkit` dans `wp-content/plugins/`.
2. Activer le plugin depuis l’admin WordPress.

== Changelog ==
= 0.2.0 =
* Implémentation des 4 outils (contraste, audit clavier, repères, médias)
* Interface d’administration enrichie (aperçus, liste des résultats, gestion des erreurs)
