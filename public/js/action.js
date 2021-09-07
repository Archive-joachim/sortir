$(function () {
    $('#city').on("change", function (event) {
        getLieux();
    });

    $('#lieux').on("change", function (event) {
        getInfoLieu();
    });
    getLieux();
});

//Recupere  la liste des lieux en fonction de la ville selectionnee, les donnees sont injectee dans la liste deroulante
function getLieux() {
    $('#lieux').prop("disabled", false);
    let ville = $("#city option:selected").val();
    let request = "/list-lieu/" + ville;
    $.ajax({
        url: request,
        method: "GET"
    }).done(function (data) {
        let result = JSON.parse(data);
        const lieux = $('#lieux');
        lieux.empty().append('<option value="">Choisissez un lieu</option>');
        for (let i = 0; i < result.length; i++) {
            lieux.append('<option value=' + result[i].id + '>' + result[i].nom + '</option>');
        }
    })
}

//Recupere les infos du lieu selectionne, les donnees sont injectees dans les champs correspondants
function getInfoLieu() {
    let id = $('#lieux').val();
    let request = "/info-lieu/" + id;
    $.ajax({
        url: request,
        method: "GET"
    }).done(function (data) {
        let result = JSON.parse(data);
        $('#street').empty().append(result.rue);
        $('#postcode').empty().append(result.ville.codePostal);
        $('#latitude').empty().append(result.latitude);
        $('#longitude').empty().append(result.longitude);
    })
}