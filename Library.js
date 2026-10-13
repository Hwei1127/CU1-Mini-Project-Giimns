const openId = new URLSearchParams(window.location.search).get("open");

if (openId) {
    const card = document.getElementById("card-" + openId);
    if (card) {
        card.showPopover();
    }
    history.replaceState(null, "", "Library.php");
}