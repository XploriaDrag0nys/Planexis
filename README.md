✅	Prévoir un affichage de 10 lignes par tableau (pages suivantes/précédents…) et possibilité d’afficher toutes les lignes
✅Tri et recherche de mots clé sur chaque colonne

Page d’authentification :
✅	Login + mdp avec protection contre brut force
✅	Pour une version ult. : Authentification forte

Administration
-	Un profil ADMIN permettra de créer les différents projets et les droits d’accès associé à des profils d’accès, et peut agir sur tous les projets en cours. 1 projet peut donc contenir plusieurs actions.
-	Profils d’accès
o	Chef de projet : il peut gérer le suivi d’actions de chaque projet (créer, supprimer, …) et peut paramétrer chaque projet : métriques, indicateurs, …
o	Contributeur : il peut éditer les lignes pour lesquelles il a une action affectée pour gérer son avancement et saisie d’information. Il peut uniquement lire les actions pour lesquelles il n’est pas affecté afin d’avoir une vision globale des actions du projet
-	Les métriques par défaut chaque nouveau projet et paramétrables pour chaque projet sont :
o	Type priorité et (nb de jours de retard toléré) : P0(0), P1(30), P2(60), P3 (pas de suivi de retard, Best Effort). Le nombre de Priorité doit être paramétrable pour chaque projet par les chefs de projet en sachant qu’il y aura toujours la dernière priorité en « Best effort ».
o	Pour chaque priorité (hors Best Effort), le nb de jours mettant en évidence l’approche de l’échéance : P0(30), P1(20), P2(10)
o	Les objectifs de performance
	Le % global minimum du respect des échéances (quelques soit la priorité), par défaut de 80%
	Le % minimum pour P0 par défaut à 90%
	Le % minimum pour P1 par défaut à 80%
	Le % minimum pour P2 par défaut à 70%
o	Statut action : Non commencé, En pause, En cours, Terminé (les statuts « Non commencé » et « Terminé » ne sont pas modifiables.
o	Sources action : Analyse de risques, Audit de certification, Audit interne, Contrôle interne, Audit technique, Incident de sécurité, Opportunité d'amélioration

Calcule des indicateurs
-	Pour chaque action un indicateur de performance est affiché sous forme d’une illustration à définir et est calculé selon les priorités et nb de jours de retard paramétré
-	Pour chaque projet un indicateur de performance affichant à l’instant « t »
o	Le % global de conformité du respect des échéances (quelques soit la priorité). A afficher quelque part en haut de la page.
	Base de calcul du % à prendre en compte : 
•	Toutes les actions sauf celles en « Best Effort »
•	Sur les 12 derniers mois glissants ou plus si action en statut différent « Terminé »
•	Nb total d’actions terminés et respectant l’objectif
•	Rouge si inférieur au seuil, Vert si supérieur ou égal
o	La même chose pour les P0, P1, P2 (3 indicateurs donc à afficher qui donneront du détail à l’indicateur global)
o	Un indicateur global de suivi mensuel mettant en évidence à chaque 1er du mois le % global de conformité. A afficher sous forme de graphique mettant en évidence les 12 derniers mois glissants, l’objectif défini et la tendance. Ce graphique pourra être affiché dans une fenêtre contextuelle.
Par exemple : 
 

o	Pour chaque action (chaque ligne), 
	un indicateur de couleur mettant en évidence l’approche de l’échéance selon le paramétrage défini par le chef de projet : vert (tvb), orange (le nb de j défini est atteint, rouge il est dépassé
	un indicateur de couleur pour chaque action terminée mettant en évidence si l’échéance a été respectée


