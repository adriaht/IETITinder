  // Delete all html inside the main content Div
  const container = document.getElementById('content');
  container.innerHTML = ''; 

  // IMAGE 
  // TE FALTARÁ EL USER O DONDE SEA QUE TENGAS LA IMAGEN
  const image = document.createElement('img');
  image.src = user.photos[0]; // ARRAY
  image.alt =`photo_of_${user.info.alias}`;

  /* IMAGE CARROUSELL*/

  /* LOS BOTONCITOS*/ 
  let carrouselContainer = null;

  if (user.photos.length > 1) {

      carrouselContainer = document.createElement('div');
      carrouselContainer.id = "carrousel-container";

      let currentIndex = 0;
      const dots = [];

      user.photos.map((photo , i) => {
          const dot = document.createElement('span');
          dot.classList.add('dot');
          dots.push(dot);
          if (i === 0) dot.classList.add('active');
          carrouselContainer.appendChild(dot);
      })

      console.log(dots);

      carrouselContainer.addEventListener("click", handleCarouselClick) 

      function handleCarouselClick() {
        
          currentIndex += 1;

          if (currentIndex >= user.photos.length) {
              currentIndex = 0;
          }
  
          image.src = user.photos[currentIndex];
  
          dots.map(dot => dot.classList.remove('active'));
          dots[currentIndex].classList.add('active');
      }

  }

  /*CONTAINER OF INFO -> search*/
  const infoContainer = document.createElement('div');
  infoContainer.id = "info-container";
  const nameText = document.createElement('h2');
  nameText.innerText = user.info.name;
  const ageText = document.createElement('h3');
  ageText.innerText = user.info.age;
  infoContainer.appendChild(nameText);
  infoContainer.appendChild(ageText);



   container.appendChild(image);
   if(carrouselContainer) {
       container.appendChild(carrouselContainer);
   }
   container.appendChild(infoContainer);


main {

    height: 688px;
    position: relative;

}

// CSS
main.discover img {

    width: 100%;
    height: 92%;
    object-fit: cover;
    object-position: center;

}

main.discover #carrousel-container {

    position: absolute;
    top: 460px;
    left: 50%; /* Move the element to the center based on its left edge */
    transform: translateX(-50%); /* Offset the element by half of its width */
    
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 10px;
    color: white;

    backdrop-filter: blur(10px);
    padding: 10px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.5); /* Optional: add shadow */
    border-radius: 5px;

    font-family: "Poppins";

    text-align: center;
    margin-top: 10px;
}

main.discover #carrousel-container span.dot {

    height: 10px;
    width: 10px;
    margin: 0 5px;
    background-color: gray;
    border-radius: 50%;
    display: inline-block;
    cursor: pointer;

}

main.discover #carrousel-container span.dot.active {
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2); /* Optional: add shadow */
    background-color: white;
}


main.discover #info-container {

    position: absolute;
    top: 74%;
    left: 50%; /* Move the element to the center based on its left edge */
    transform: translateX(-50%); /* Offset the element by half of its width */
    
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 30px;
    color: white;

    font-size: 1.25rem;

    backdrop-filter: blur(10px);
    padding: 15px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.5); /* Optional: add shadow */
    border-radius: 5px;

    font-family: "Poppins";
}

/* MARK: END INTEGRATION */