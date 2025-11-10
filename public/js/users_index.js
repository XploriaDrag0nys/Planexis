$(document).ready(function () {
    $('#users-table').DataTable({
        order: [[3, 'desc']], // tri sur date de création
        language: {
            search: "🔍 Rechercher :",
            lengthMenu: "Afficher _MENU_ utilisateurs",
            zeroRecords: "Aucun utilisateur trouvé",
            info: "Affichage de _START_ à _END_ sur _TOTAL_",
            infoEmpty: "Aucun utilisateur disponible",
            infoFiltered: "(filtré sur _MAX_ au total)",
            paginate: {
                first: "Premier",
                previous: "Précédent",
                next: "Suivant",
                last: "Dernier"
            }
        }
    });
});
