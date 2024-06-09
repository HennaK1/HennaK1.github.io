const images = document.querySelectorAll(".gallery__item img");
const form = document.getElementById("fcf-form-id");
if (form) {
  form.addEventListener("submit", SubmitForm);
}
let imgIndex;
let imgSrc;

// Get images src onclick
images.forEach((img, i) => {
  img.addEventListener("click", (e) => {
    let targetImg = e.target;
    // Show image (could be in a different way now, such as in a dedicated section)
    showImage(targetImg);
    // Index of the next image
    imgIndex = i;
  });
});

// Function to show the image (simplified without modal)
let showImage = (aImage) => {
  const displaySection = document.querySelector(".image-display");
  if (displaySection) {
    displaySection.innerHTML = ""; // Clear previous content
    const img = document.createElement("img");
    img.setAttribute("src", aImage.src);
    const altText = document.createElement("div");
    altText.innerHTML = aImage.alt;
    altText.setAttribute("class", "caption");
    displaySection.append(img, altText);
  }
};

// Next image function
let nextImg = () => {
  imgIndex++;
  // Check if it is the last image
  if (imgIndex >= images.length) {
    imgIndex = 0;
  }
  // Return next image
  return images[imgIndex];
};

// Previous image function
let prevImg = () => {
  imgIndex--;
  // Check if it is the first image
  if (imgIndex < 0) {
    imgIndex = images.length - 1;
  }
  // Return previous image
  return images[imgIndex];
};

// Form Submit Successfully
function SubmitForm(e) {
  if (e) {
    e.preventDefault();
  }
  console.log(e);
  let formData = new FormData(form);
  fetch("/contact-form-process.php", { method: "POST", body: formData })
    .then(function (response) {
      console.log(response);
      form.reset();
    })
    .then(function (body) {
      console.log(body);
    });
}
