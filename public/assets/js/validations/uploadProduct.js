    validateUpload = () => {

        var title = document.forms["upload_form"]["title"];
        var price = document.forms["upload_form"]["price"];
        var cat = document.forms["upload_form"]["cat"];
        var desc = document.forms["upload_form"]["desc"];
        var files = document.forms["upload_form"]["files[]"];

        const ALLOWED_EXTENSIONS = /(\jpg|\png|\jpeg)$/i;
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
        
        if ($(files).val() == ""){
            $("#lblFile").addClass('is-invalid');
            $(files).addClass('is-invalid');
            valid = false;
        }else{
            var validImg = true;
            for (let i = 0; i < $(files)[0].files.length; i++){
                let file = $(files)[0].files[i].name;
                var filename  = file.pop();       // Take the last element. This is a file name: 'file.ext'
                var filenameParts = filename.split('.'); // Split the file name on the '.': ['file', 'ext']
                var extension = filenameParts[1];      // Take the extension: 'ext'
                if(!ALLOWED_EXTENSIONS.exec(extension)){
                    $("#lblFile").addClass('is-invalid');
                    $(files).addClass('is-invalid');
                    $('#feebackFile').text("Extension invalid, Please upload pictures with extension JPG or PNG");
                    valid = false;
                    validImg = false;
                }
                if(($(files)[0].files[i].size / 1024) > 1000) {
                    $('#feebackFile').text("Size of file too big, please upload pictures that are less than 1MB");
                    $("#lblFile").addClass('is-invalid');
                    $(files).addClass('is-invalid');
                    valid = false;
                    validImg = false;
                }
            }
            if(validImg == true && $("#lblFile").hasClass('is-invalid')){
                $("#lblFile").removeClass('is-invalid');
                $(files).removeClass('is-invalid');
            }
        }
        return valid;
    }