function copyDivToClipboard() {
    var range = document.createRange();
    range.selectNode(document.getElementById("cleancopy"));
    window.getSelection().removeAllRanges(); // clear current selection
    window.getSelection().addRange(range); // to select text
    document.execCommand("copy");
    window.getSelection().removeAllRanges();// to deselect
    alert("Copied");
}

tinymce.init({
selector: '#mytextarea',
toolbar: 'undo redo | formatpainter casechange blocks | bold italic backcolor | ' +
    'alignleft aligncenter alignright alignjustify | ' +
    'bullist numlist checklist outdent indent | removeformat | a11ycheck code table help'
});

const button = document.querySelector("#showoptions");
const optionsarea = document.querySelector("#options");
if(button) {
    button.addEventListener("click", e => {
        console.log(e);
        optionsarea.style.display = 'block';
        button.style.display = 'none';
      });      
}
