const elements = {
    pokemonImage: document.getElementById('pokemonImage'),
    formReponse: document.getElementById('formReponse'),
    jeuDiv: document.getElementById('containerJeu'),
    scoreJoueur: document.getElementById('scoreJoueur'),
    heartSound: document.getElementById('heartSound'),
    sectionJeu: document.getElementById('sectionJeu'),
    containerLogo: document.getElementById('containerLogo'),
    footer: document.getElementById('footer'),
};

const IMG_VIE_PERDUE_JOUEUR = "/build/images/viePerdueJoueur.png";

function playHeartSound() {
    elements.heartSound.currentTime = 0;
    elements.heartSound.play();
}

function afficherNouveauPokemon(imageSrc, nouveauScore) {
    elements.pokemonImage.src = imageSrc;
    elements.scoreJoueur.textContent = nouveauScore;
    elements.formReponse.querySelector('button[type="submit"]').disabled = false;
}

function afficherViePerdueEtNouveauPokemon(data) {
    const vieElements = document.querySelectorAll('[id^="vie"]');
    const dernierVie = Array.from(vieElements).find(vie => vie.id.startsWith("looseVie")) || vieElements[vieElements.length - 1];

    afficherNouveauPokemon(data.image, data.score);
    dernierVie.src = IMG_VIE_PERDUE_JOUEUR;
    playHeartSound();
    dernierVie.id = "looseVie";
    elements.formReponse.querySelector('button[type="submit"]').disabled = false;
}

function changerStylesEtValeurs(data) {
    const bouton = document.getElementById('boutonForm');
    const label = document.querySelector('label[for="reponse"]');
    const reponseUtilisateur = document.getElementById('reponse');

    bouton.style.display = 'none';
    label.textContent = 'Réponse';
    label.style.fontSize = '1.3rem';
    label.style.fontWeight = 'bold';
    reponseUtilisateur.value = data.reponsePokemon;
    reponseUtilisateur.disabled = true;
    reponseUtilisateur.style.backgroundColor = '#DC6661';
    reponseUtilisateur.style.color = 'white';
    reponseUtilisateur.style.fontWeight = 'bold';
}

function reinitialiserStylesEtValeurs() {
    const bouton = document.getElementById('boutonForm');
    const label = document.querySelector('label[for="reponse"]');
    const reponseUtilisateur = document.getElementById('reponse');

    // Réinitialiser les styles du bouton et de la zone de saisie
    bouton.style.display = 'inline-block';
    reponseUtilisateur.style.backgroundColor = 'white';
    reponseUtilisateur.style.color = 'black';
    reponseUtilisateur.style.fontWeight = 'normal';

    // Réinitialiser les styles du label
    label.textContent = 'Entrer votre réponse';
    label.style.fontSize = 'inherit';
    label.style.fontWeight = 'normal';

    // Réinitialiser la zone de saisie
    reponseUtilisateur.value = '';
    reponseUtilisateur.disabled = false;
}

function afficherEcranLoose(data) {
    const divContainerLoose = document.createElement('div');
    divContainerLoose.className = 'containerLoose';
    divContainerLoose.innerHTML = data.loose;
    elements.sectionJeu.appendChild(divContainerLoose);
    elements.jeuDiv.remove();

    const scriptLoose = document.createElement('script');
    scriptLoose.src = "/build/js/loose.js";
    document.body.appendChild(scriptLoose);

    elements.sectionJeu.className = 'sectionJeuEnd';
    elements.containerLogo.remove();
}

function afficherEcranWin(data) {
    const divContainerWin = document.createElement('div');
    divContainerWin.className = 'containerWin';
    divContainerWin.innerHTML = data.winner;
    elements.sectionJeu.appendChild(divContainerWin);
    elements.jeuDiv.remove();
    elements.footer.remove();

    const scriptWin = document.createElement('script');
    scriptWin.src = "/build/js/loose.js";
    document.body.appendChild(scriptWin);

    elements.sectionJeu.className = 'sectionJeuEnd';
    elements.containerLogo.remove();
}

elements.formReponse.addEventListener('submit', event => {
    event.preventDefault();

    // Désactiver le bouton pour empêcher un double clic
    elements.formReponse.querySelector('button[type="submit"]').disabled = true;

    const reponseUtilisateur = document.getElementById('reponse');
    const nouveauPokemonUrl = elements.formReponse.dataset.url;

    fetch(nouveauPokemonUrl, {
        method: 'POST',
        body: new URLSearchParams({ reponse: reponseUtilisateur.value }),
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
    })
        .then(response => response.json())
        .then(data => {
            if (data.resultat === 'winGame') {
                afficherEcranWin(data);
            } else {
                if (data.resultat === true && data.nombreVie > 0) {
                    afficherNouveauPokemon(data.image, data.score);
                    reponseUtilisateur.value = '';
                } else {
                    if (data.nombreVie > 0) {
                        changerStylesEtValeurs(data);

                        setTimeout(() => {
                            reinitialiserStylesEtValeurs();
                            afficherViePerdueEtNouveauPokemon(data);
                        }, 3000);
                    } else {
                        elements.footer.remove();
                        afficherViePerdueEtNouveauPokemon(data);
                        elements.heartSound.addEventListener('ended', () => {
                            afficherEcranLoose(data);
                        });
                    }
                }
            }
        });
});


function disableImageInteractions(e) {

    if (e.target.tagName === 'IMG' && e.target.id === 'pokemonImage') {
        e.preventDefault();
    }
}

document.addEventListener('contextmenu', disableImageInteractions);
document.addEventListener('mousedown', disableImageInteractions);