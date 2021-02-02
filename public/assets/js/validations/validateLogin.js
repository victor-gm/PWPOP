validateLogin = () => {

    const USERNAME_ERROR = "You need to introduce an alphanumeric username";
    const USERNAME_ERROR_LENGHT = "Username can't exceed 20 characters";
    const VALID_EMAIL = "The provided email is not valid";

    var username_or_email = document.forms["login_form"]["username_or_email"];
    var pass = document.forms["login_form"]["password"];

    var valid = true;

    //Check wether is username or email
    if(username_or_email.value == "")
    {
        $(username_or_email).addClass('is-invalid');
        valid = false;
    }else if(/@/.test(username_or_email.value)) //if it's an email
    {
        if(!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(username_or_email.value)){
            $(username_or_email).addClass('is-invalid');
            $("#emailError").text(VALID_EMAIL);
            valid = false;
        }else if($(username_or_email).hasClass('is-invalid')){
            $(username_or_email).removeClass('is-invalid');
        }
    }else //if it's a username
    {
        if(!/^[a-zA-Z0-9]+$/.test(username_or_email.value)){
            $(username_or_email).addClass('is-invalid');
            $("#usernameError").text(USERNAME_ERROR);
            valid = false;
        }else if(username_or_email.value.length > 20){
            $(username_or_email).addClass('is-invalid');
            $("#usernameError").text(USERNAME_ERROR_LENGHT);
            valid = false;
        }else if($(username_or_email).hasClass('is-invalid')){
            $(username_or_email).removeClass('is-invalid');
        }
    }
    if (pass.value == "") {
        $(pass).addClass('is-invalid');
        valid = false;
    }else if(pass.value.length < 6){
        $(pass).addClass('is-invalid');
        $("#loginPasswordError").text("Password must be at least 6 characters long");
        valid = false;
    }else if($(pass).hasClass('is-invalid')){
        $(pass).removeClass('is-invalid');
    }

    return valid;

}