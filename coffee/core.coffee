$ ->
  $('.page-scroll').smoothScroll
    offset: 0
    speed: 800
    afterScroll: ->
      # in mobile version hide menu if open
      $target = $('.navbar-collapse')
      if $target.hasClass('collapse show')
        $target.collapse('hide')


  if $('#grid').length > 0
    mixer = mixitup '#grid',
      controls:
        toggleLogic: 'and'
        enable: true
      selectors:
        target: '.grid-item'
      animation:
        duration: 250
        nudge: false
        reverseOut: false
        effects: 'fade'
        #enable: false
      callbacks:
        onMixFail: (state) ->
          console.log('No items could be found matching the requested filter')
          $('#empty-list').collapse('show')
        onMixEnd: (state) ->
          $alert = $('#empty-list')
          if state.totalShow > 0 && $alert.hasClass('show')
            $alert.collapse('hide')

