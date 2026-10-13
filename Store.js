/* Search box and genre buttons (used on Store.php).
   Both feed into one filter that hides the games that do not match. */
let activeGenre = "";

function applyFilters() {
    const input = document.getElementById("myInput");
    const filter = input.value.trim().toLowerCase();
    const tiles = document.querySelectorAll("#myUL .game-tile");
    let visible = 0;

    tiles.forEach(function (tile) {
        const name = tile.dataset.name || "";
        const genres = tile.dataset.genres || "";
        const matchesText = name.includes(filter) || genres.includes(filter);
        const matchesGenre = !activeGenre || genres.split(",").includes(activeGenre);

        if (matchesText && matchesGenre) {
            tile.style.display = "";
            visible++;
        } else {
            tile.style.display = "none";
        }
    });

    document.getElementById("noResults").hidden = visible !== 0;
}

// Kept under the old name so onkeyup="myFunction()" in Store.php still works
function myFunction() {
    applyFilters();
}

document.querySelectorAll("#genreFilter .chip").forEach(function (chip) {
    chip.addEventListener("click", function () {
        document.querySelectorAll("#genreFilter .chip").forEach(function (other) {
            other.classList.remove("active");
        });
        chip.classList.add("active");
        activeGenre = chip.dataset.genre;
        applyFilters();
    });
});

/* After a review or the wishlist is saved, Store.php?open=ID loads again.
   Open the card of that game so the message is visible. */
const openId = new URLSearchParams(window.location.search).get("open");

if (openId) {
    const card = document.getElementById("card-" + openId);
    if (card) {
        card.showPopover();
    }
    history.replaceState(null, "", "Store.php");
}