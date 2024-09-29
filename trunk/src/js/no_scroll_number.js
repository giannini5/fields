// https://stackoverflow.com/questions/9712295/disable-scrolling-on-input-type-number
// When collecting a number, disable scroll so that you can scroll the page after entering the
// number and the number will not change
document.addEventListener("wheel", function(event){
    if(document.activeElement.type === "number"){
        document.activeElement.blur();
    }
});
