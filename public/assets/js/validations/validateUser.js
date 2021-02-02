validateUser = () => {
    const NAME_ERROR = 'You need to introduce an alphanumeric name';
    const USERNAME_ERROR = "You need to introduce an alphanumeric username";
    const USERNAME_ERROR_LENGHT = "Username can't exceed 20 characters";
    const VALID_EMAIL = "The provided email is not valid";

    var name = document.forms["register_form"]["name"];
    var username = document.forms["register_form"]["username"];
    var email = document.forms["register_form"]["email"];
    var birthdate = document.forms["register_form"]["birthdate"];
    var tel = document.forms["register_form"]["phone"];
    var pass = document.forms["register_form"]["password"];
    var confirm = document.forms["register_form"]["confirmPassword"];
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

    if (username.value == "") {
        $(username).addClass('is-invalid');
        valid = false;
    }else if(!/^[a-zA-Z0-9]+$/.test(username.value)){
        $(username).addClass('is-invalid');
        $("#usernameError").text(USERNAME_ERROR);
        valid = false;
    }else if(username.value.length > 20){
        $(username).addClass('is-invalid');
        $("#usernameError").text(USERNAME_ERROR_LENGHT);
        valid = false;
    }else if($(username).hasClass('is-invalid')){
        $(username).removeClass('is-invalid');
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
            $("#birthdayError").addClass('is-invalid');
            valid = false;
        }else if($(birthdate).hasClass('is-invalid')){
            $(username).removeClass('is-invalid');
            $("#birthdayError").removeClass('is-invalid');
        }
    }else if($(birthdate).hasClass('is-invalid')){
        $(username).removeClass('is-invalid');
        $("#birthdayError").removeClass('is-invalid');
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
    
    if (pass.value == "") {
        $(pass).addClass('is-invalid');
        valid = false;
    }else if(pass.value.length < 6){
        $(pass).addClass('is-invalid');
        $("#passwordError").text("Password must be at least 6 characters long");
        valid = false;
    }else if($(pass).hasClass('is-invalid')){
        $(pass).removeClass('is-invalid');
    }

    if (confirm.value == "") {
        $(confirm).addClass('is-invalid');
        valid = false;
    }else if(confirm.value != pass.value){
        $(confirm).addClass('is-invalid');
        valid = false;
    }else if($(confirm).hasClass('is-invalid')){
        $(confirm).removeClass('is-invalid');
    }
    return valid;
}