validateProfile = () => {
    const NAME_ERROR = 'You need to introduce an alphanumeric name';
    const USERNAME_ERROR = "You need to introduce an alphanumeric username";
    const USERNAME_ERROR_LENGHT = "Username can't exceed 20 characters";
    const VALID_EMAIL = "The provided email is not valid";

    var name = document.forms["edit_profile_form"]["name"];
    var email = document.forms["edit_profile_form"]["email"];
    var birthdate = document.forms["edit_profile_form"]["birthdate"];
    var tel = document.forms["edit_profile_form"]["phone"];
    var pass = document.forms["edit_profile_form"]["password"];
    var confirm = document.forms["edit_profile_form"]["confirmPassword"];
    var valid = true;

    //name --> required field
    if (name.value == "") {
        $(name).addClass('is-invalid');
        valid = false;
    }else if(!/^[a-zA-Z0-9]+$/.test(name.value)){
        $(name).addClass('is-invalid');
        $("#nameError").text(NAME_ERROR);
        valid = false;
    }else if($(name).hasClass('is-invalid')){
        $(name).removeClass('is-invalid');
    }

    if (email.value == "") {
        $(email).addClass('is-invalid');
        valid = false;
    }else if(!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)){
        $(email).addClass('is-invalid');
        $("#emailError").text(VALID_EMAIL);
        valid = false;
    }else if($(email).hasClass('is-invalid')){
        $(email).removeClass('is-invalid');
    }
    if (birthdate.value != "") {
        if(!/^\d{1,2}\-\d{1,2}\-\d{4}$/.test(birthdate.value)){
            $(birthdate).addClass('is-invalid');
            $("#birthdayErrorProfile").addClass('is-invalid');
            valid = false;
        }else if($(birthdate).hasClass('is-invalid')){
            $(username).removeClass('is-invalid');
            $("#birthdayErrorProfile").removeClass('is-invalid');
        }
    }else if($(birthdate).hasClass('is-invalid')){
        $(username).removeClass('is-invalid');
        $("#birthdayErrorProfile").removeClass('is-invalid');
    }

    if (tel.value == "") {
        $(tel).addClass('is-invalid');
        valid = false;
    }else if(!/^\d{3}\-\d{3}\-\d{3}/.test(tel.value)){
        $(tel).addClass('is-invalid');
        valid = false;
    }else if($(tel).hasClass('is-invalid')){
        $(tel).removeClass('is-invalid');
    }
    
    if (pass.value != "") {
        if(pass.value.length < 6){
            $("#profilepwd").addClass('is-invalid');
            valid = false;
        }else if($("#profilepwd").hasClass('is-invalid')){
            $("#profilepwd").removeClass('is-invalid');
        }
    }else if($("#profilepwd").hasClass('is-invalid')){
        $("#profilepwd").removeClass('is-invalid');
    }

    if (pass.value != "" || confirm.value != "") {
        if(confirm.value != pass.value){
        $(confirm).addClass('is-invalid');
        valid = false;
        }else if($(confirm).hasClass('is-invalid')){
            $(confirm).removeClass('is-invalid');
        }
    }else if($(pass).hasClass('is-invalid')){
        $(pass).removeClass('is-invalid');
    }
    return valid;
    
}