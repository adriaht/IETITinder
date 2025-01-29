

document.addEventListener("DOMContentLoaded", (event) => {

    console.log("DOM fully loaded and parsed");
    console.log(userPhotos);

    const imageContainer = document.getElementById("user-photos");
    console.log(imageContainer);

    const image = document.getElementById('user-image');
    image.src = userPhotos[0].path;

    let carrouselContainer = null;
    if (userPhotos.length > 1) {
        carrouselContainer = document.createElement('div');
        carrouselContainer.id = "carrousel-container";
        let currentIndex = 0;
        const dots = [];
        userPhotos.map((photo , i) => {
            const dot = document.createElement('span');
            dot.classList.add('dot');
            dots.push(dot);
            if (i === 0) dot.classList.add('active');
            carrouselContainer.appendChild(dot);
        })
       // console.log(dots);
        carrouselContainer.addEventListener("click", handleCarouselClick) 
        function handleCarouselClick() {
            
            currentIndex += 1;
            if (currentIndex >= userPhotos.length) {
                currentIndex = 0;
            }
            image.src = userPhotos[currentIndex].path;
            dots.map(dot => dot.classList.remove('active'));
            dots[currentIndex].classList.add('active');
        }
    }
    
    console.log(carrouselContainer);
    if(carrouselContainer) {
        imageContainer.appendChild(carrouselContainer);
    }

  });