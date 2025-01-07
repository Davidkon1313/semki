document.addEventListener("DOMContentLoaded", function () {
    const screenWidth = window.innerWidth;
    const storedScreenWidth = localStorage.getItem("screen_width");

    if (!storedScreenWidth || storedScreenWidth != screenWidth) {
        localStorage.setItem("screen_width", screenWidth);
        // Reload the page to ensure PHP has access to the screen size
        location.reload();
    }
});