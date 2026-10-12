/* Drag and drop image upload with a live preview (used on Admin_Create.php).
   Works for every .drop-area on the page: one for the cover, one for the banner. */
document.querySelectorAll(".drop-area").forEach(function (dropArea) {
    const inputFile = dropArea.querySelector("input[type=file]");
    const imageView = dropArea.querySelector(".img-view");

    function uploadImage() {
        const file = inputFile.files[0];
        if (!file) return;

        if (!file.type.startsWith("image/")) {
            inputFile.value = "";
            imageView.style.backgroundImage = "";
            imageView.classList.remove("has-image");
            imageView.textContent = "That file is not an image. Try another one.";
            return;
        }

        const imgLink = URL.createObjectURL(file);
        imageView.style.backgroundImage = `url(${imgLink})`;
        imageView.textContent = "";
        imageView.classList.add("has-image");
    }

    inputFile.addEventListener("change", uploadImage);

    dropArea.addEventListener("dragover", function (e) {
        e.preventDefault();
        dropArea.classList.add("dragging");
    });

    dropArea.addEventListener("dragleave", function () {
        dropArea.classList.remove("dragging");
    });

    dropArea.addEventListener("drop", function (e) {
        e.preventDefault();
        dropArea.classList.remove("dragging");
        inputFile.files = e.dataTransfer.files;
        uploadImage();
    });
});