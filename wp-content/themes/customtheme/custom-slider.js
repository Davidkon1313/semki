document.addEventListener("DOMContentLoaded", function () {
    const carouselTrack = document.querySelector('.carousel-track');
    const prevBtn = document.querySelector('.prev-btn');
    const nextBtn = document.querySelector('.next-btn');
    const carouselItems = document.querySelectorAll('.carousel-item');

    // Function to set active class on the central item
    function setActiveItem() {
        const itemWidth = carouselItems[0].offsetWidth;
        const trackWidth = carouselTrack.offsetWidth;
        const itemsCount = carouselItems.length;

        // Calculate which item is central
        let centerIndex = Math.floor(trackWidth / 2 / itemWidth);
        // Adjust for odd/even number of items
        if (carouselItems.length % 2 === 0) {
            centerIndex = Math.floor((trackWidth / 2 + itemWidth / 2) / itemWidth);
        }
        centerIndex = 2;

        // Remove active class from all items
        carouselItems.forEach(item => item.classList.remove('active'));

        // Add active class to the central item
        const centralItem = carouselTrack.children[centerIndex];
        if (centralItem) {
            centralItem.classList.add('active');
        }
    }

    // Function to move to the next item
    function moveToNextItem() {
        const itemWidth = carouselItems[0].offsetWidth;
        carouselTrack.style.transition = 'transform 0.3s ease';
        carouselTrack.style.transform = `translateX(-${itemWidth}px)`;

        // After transition ends, reset position if we're at the cloned first item
        setTimeout(() => {
            const firstItemInTrack = carouselTrack.firstElementChild;
            carouselTrack.style.transition = 'none';
            carouselTrack.style.transform = `translateX(0)`;
            carouselTrack.appendChild(firstItemInTrack); // Move the first item to the end
            setActiveItem(); // Update the active class
        }, 300);
    }

    // Function to move to the previous item
    function moveToPrevItem() {
        const itemWidth = carouselItems[0].offsetWidth;
        carouselTrack.style.transition = 'transform 0.3s ease';
        carouselTrack.style.transform = `translateX(${itemWidth}px)`;

        // After transition ends, reset position if we're at the cloned last item
        setTimeout(() => {
            const lastItemInTrack = carouselTrack.lastElementChild;
            carouselTrack.style.transition = 'none';
            carouselTrack.style.transform = `translateX(0)`;
            carouselTrack.insertBefore(lastItemInTrack, carouselTrack.firstChild); // Move the last item to the front
            setActiveItem(); // Update the active class
        }, 300);
    }

    // Set initial active item
    setActiveItem();

    // Event listeners for next and prev buttons
    nextBtn.addEventListener('click', moveToNextItem);
    prevBtn.addEventListener('click', moveToPrevItem);
});