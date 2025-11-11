function muncul(e){
    const target = $(e.target)

    if($(target).hasClass("active")){
        $(target).html("More Info").removeClass("active")
    } else {
        $(target).html("Less Info").addClass("active")
    }
    
    const main = $(target).parents(".header")
    const toogle = $(main).children(".isi")

    $(toogle).slideToggle()
}
