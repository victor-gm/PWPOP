$(document).ready(function() {
  
  bsCustomFileInput.init()
  $('.da-datepicker').datepicker({
    uiLibrary: 'bootstrap4',
    format: 'dd-mm-yyyy'
  });
  $('.da-phoneinput').mask('000-000-000');

  $('.img-carousel').slick({
    dots: false,
    arrows: true, 
    infinite: true,
    slidesToShow: 2,
    slidesToScroll: 1
  });

  if($("#registerSuccessful").val() != undefined){
    $('#loginModal').modal('show');
  }
  if($("#registerErrors").val() != undefined){
    $('#signupModal').modal('show');
  }
  if($("#loginErrors").val() != undefined){
    $('#loginModal').modal('show');
  }
  
  enableEdit = (toggle = false) =>{
    $('#form_title').prop('disabled', toggle);
    $('#form_price').prop('disabled', toggle);
    $('#form_desc').prop('disabled', toggle);
    $('#form_cat').prop('disabled', toggle);
    if(!toggle){
      $('#editBtn').hide();
      $('#deleteBtn').hide();
      $('#submitBtn').show();
    }else{
      $('#editBtn').show();
      $('#deleteBtn').show();
      $('#submitBtn').hide();
    }
  }
  enableEditProfile = (toggle = false) =>{
    $('#profile_name').prop('disabled', toggle);
    $('#profile_email').prop('disabled', toggle);
    $('#profile_phone').prop('disabled', toggle);
    $('#profile_birthdate').prop('disabled', toggle);
    // $('#profile_birthdate').addClass('da-datepicker');
    $('#profile_birthdate').datepicker({
      uiLibrary: 'bootstrap4',
      format: 'dd-mm-yyyy'
    });
    $('#btnsEditing').toggleClass('dn');
    $('#btnsNotEditing').toggleClass('dn');
    $('#ppchange').toggleClass('dn');
    $('#new-pwd').toggleClass('dn');
    $('#new-pwd-confirm').toggleClass('dn');
  }

  loadMoreProducts = (session) => {
    var offsetProdVal = eval($('#offsetProducts').val());
    offsetProdVal += 1;
    $('#offsetProducts').val(offsetProdVal);
    var offset = eval($('#offsetProducts').val()) * 5;
    $.ajax({
      type: 'GET',
      url: '/getProducts/'+offset,
      contentType: 'application/json;charset=utf-8',
      dataType: 'json'
    })
    .done(function(data) {
        var searchLimit = $.grep(data, function(e){ return e.limit === true});
        if(searchLimit.length > 0){
          if(searchLimit[0].limit == true){
            $('#loadMoreBtn').attr("disabled", true);
            $('#loadMoreBtn').text("No more products.");
            data.pop();
          }
        }
        data.forEach(function(product) {
          $("#homepage-products").append($('<div id=product'+product.id+' class="col-12 col-sm-6 col-md-4 col-lg-3 mt3 mb3"></div>'));
          $("#product"+product.id).append($('<div class="card h-100p center"></div>'));
          $("#product"+product.id+" .card").append($('<a class="text-center h-200 flex" href=/product/'+product.id+'></a>'));
          $("#product"+product.id+" .card a")
          .append($('<img class="img-item img-thumbnail" src="'+window.location.origin+'/uploads/'+product.image+'" alt="'+product.title+'">'));
          $("#product"+product.id+" .card").append($('<div class="card-body"></div>'));
          $("#product"+product.id+" .card .card-body")
            .append($('<h4 class="font-weight-bold">'+product.price+' €</h4><span class="card-title">'+product.title+'</span><p class="card-text">'+product.description+'</p>'));
          if(session){
            $("#product"+product.id+" .card .card-body")
              .append($('<a class="btn btn-primary" href="/buy/'+product.id+'">Buy Product</a>'))
            $("#product"+product.id+" .card .card-body")
              .append($('<a class="ml4" onclick="setFavorite('+product.id+')" id="fav'+product.id+'">'))
            if(product.favorite) {
              $("#product"+product.id+" .card .card-body #fav"+product.id+"")
                .append($('<img src="'+window.location.origin+'/assets/img/heartfilled.svg" alt="Make product favorite">'))
            }else{
              $("#product"+product.id+" .card .card-body #fav"+product.id+"")
                .append($('<img src="'+window.location.origin+'/assets/img/heart.svg" alt="Make product favorite">'))
            }
          }
        });
    })
    .fail(function(error) {
    });
  }

  loadModal = () => {
    var categoryOpts = document.getElementById('categorySelect').options;
    if(categoryOpts.length == 1){
      $.ajax({
        type: 'GET',
        url: '/getCategories',
        contentType: 'application/json;charset=utf-8',
        dataType: 'json'
      })
      .done(function(data) {
        var optionsAsString = "";
        for(var i = 0; i < data.length; i++) {
          optionsAsString += "<option value='" + data[i]['id'] + "'>" + data[i]['name'] + "</option>";
        }
        $('#categorySelect').append( optionsAsString );
      })
      .fail(function(error) {
      });
    }
  }

  setFavorite = (product_id) => {
    $.ajax({
      type: 'POST',
      url: '/favorite/' + product_id,
      contentType: 'application/json;charset=utf-8',
      dataType: 'json'
    }).done(function(data) {
      if(data.fav){
        $("#fav"+product_id+" img").attr("src",window.location.origin+'/assets/img/heartfilled.svg');
      }else{
        $("#fav"+product_id+" img").attr("src",window.location.origin+'/assets/img/heart.svg');
      }
    })
  }

});
