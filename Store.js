// Search
let activeGenre = "";

function applyFilters() {
    const input = document.getElementById("searchInput");
    const filter = input.value.trim().toLowerCase();
    const tiles = document.querySelectorAll("#gameGrid .game-tile");
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

// Popover
const openId = new URLSearchParams(window.location.search).get("open");

if (openId) {
    const card = document.getElementById("card-" + openId);
    if (card) {
        card.showPopover();
    }
    history.replaceState(null, "", "Store.php");
}