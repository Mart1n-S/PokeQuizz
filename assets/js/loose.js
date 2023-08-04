const imageContainer = document.getElementById("imageContainer");
const image = document.getElementById("image");
const formEndGame = document.getElementById("formEndGame");
const children = formEndGame.children;

imageContainer.addEventListener("animationend", () => {
    image.classList.add("hidden");
    for (const child of children) {
        child.style.opacity = 0;
    }

    setTimeout(() => {
        image.src = '/build/images/pokeballOpen.png';
        image.classList.remove("hidden");
        for (const child of children) {
            child.style.opacity = 1;
        }
        formEndGame.style.opacity = 1;
    }, 400);
});