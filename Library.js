/* After a review or the wishlist is saved, Library.php?open=ID loads again.
   Open the card of that game so the message is visible. */
const openId = new URLSearchParams(window.location.search).get("open");

if (openId) {
    const card = document.getElementById("card-" + openId);
    if (card) {
        card.showPopover();
    }
    history.replaceState(null, "", "Library.php");
}