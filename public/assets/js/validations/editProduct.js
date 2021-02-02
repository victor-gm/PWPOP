validateEdit = () =>{
    const title = document.forms["edit_detail_form"]["title"];
    const price = document.forms["edit_detail_form"]["price"];
    const cat = document.forms["edit_detail_form"]["cat"];
    const desc = document.forms["edit_detail_form"]["desc"];
    const optionsCat = $('#form_cat > option').length;
    var valid = true;
    
    if (title.value == "") {
        $(title).addClass('is-invalid');
        valid = false;
    }else if($(title).hasClass('is-invalid')){
        $(title).removeClass('is-invalid');
    }

    if (price.value == ""){
        $(price).addClass('is-invalid');
        valid = false;
    }else if(price.value != "" && isNaN(price.value) == true){
        $(price).addClass('is-invalid');
        valid = false;
    }else if($(price).hasClass('is-invalid')){
        $(price).removeClass('is-invalid');
    }
    if (cat.value <= 0 || cat.value > (optionsCat-1)){
        $(cat).addClass('is-invalid');
        valid = false;
    }else if($(cat).hasClass('is-invalid')){
        $(cat).removeClass('is-invalid');
    }

    if (desc.value.length < 20){
        $(desc).addClass('is-invalid');
        valid = false;
    }else if($(cat).hasClass('is-invalid')){
        $(desc).removeClass('is-invalid');
    }

    return valid;
}