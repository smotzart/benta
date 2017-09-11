# Append to array, return array
Array.prototype.append = (el) ->
  this.push el if this.push?
  this

angular.module('app', [
  'vcRecaptcha', 
  'ngRoute', 
  'ngResource', 
  'ngSanitize',
  'ngAnimate',
  'angular-loading-bar',
  'slickCarousel'
])
  
  .factory('APIProducts',  ['$resource', ($resource) -> 
    $resource 'https://kernl.rocks/api/v2/public/entries/:id.json', {id: '@id', storage_id: 25}
  ])
  .factory('APICompanies',  ['$resource', ($resource) -> $resource 'https://kernl.rocks/api/v2/public/entries.json?storage_id=27'])
  .factory('APIContact',  ['$resource', ($resource) -> $resource '/misc/contact.php'])


  .run([
    '$rootScope',
  ].append (root) ->  

  )

  .config ['$routeProvider', '$locationProvider', 'cfpLoadingBarProvider', (r, l, c) ->
    
    c.includeSpinner = true

    l.html5Mode true
    l.hashPrefix ''

    r.when '/',
      controller: 'ProductsCtrl'
      templateUrl: "html/products.html"

    r.when '/:id-:name',
      controller: 'ProductCtrl'
      templateUrl: "html/product.html"

    r.otherwise
      redirectTo: '/'

  ]


  .controller('AppCtrl', ['$scope', 'vcRecaptchaService', 'APIProducts', 'APICompanies', 'APIContact', 'cfpLoadingBar', '$filter', (self, vcRecaptchaService, APIProducts, APICompanies, APIContact, cfpLoadingBar, $filter) ->
    
    self.capcha = '6Ld89C8UAAAAAKARpvwzC2nhwKuCayqtd4n5000A'

    self.companies = APICompanies.query {}, (data) ->
      results = []
      for c in data
        results.push(
          key: c.key
          title: c.title
          logo: c.logo[0].file_small_url
        )      
      self.companies = results

    self.getLogo = (company) ->
      company = $filter('filter')(self.companies, {'key': company})
      return unless company.length == 1
      return company[0].logo
    
    self.urlName = (title) ->
      return title.toLowerCase()
                  .replace(/ /g,'-')
                  .replace(/[^\w-]+/g,'')

    (self.resetContact = ->
      self.contactform.$setPristine() if self.contactform
      self.contact = new APIContact
        response: ''
        name: ''
        email: ''
        message: ''
    )()

    self.loadContact = false
    self.sendContact = ->
      self.loadContact = true
      self.contact.$save {},((data) ->
        self.loadContact = false
        self.resetContact()
        vcRecaptchaService.reload()
      ),(->
        self.loadContact = false
      )        

  ])

  .controller('ProductsCtrl', ['$scope', 'APIProducts', 'APICompanies', (self, APIProducts, APICompanies) ->
    APIProducts.query {}, (data) ->
      self.products = data    

    filter = 
      category: []
      company: []

    self.toggleFilter = (key, value) ->
      if filter[key].indexOf(value) == -1
        filter[key].push(value)
      else
        filter[key].splice(filter[key].indexOf(value), 1)
      console.log filter[key]

    self.toggleClass = (key, value) ->
      return false if filter[key].indexOf(value) == -1
      return true

    self.filterProducts = (item) ->
      return true if filter.category.length == 0 && filter.company.length == 0
      return true if filter.category.indexOf(item.category) != -1 || filter.company.indexOf(item.company) != -1
      return false

  ])

  .controller('ProductCtrl', ['$scope', '$routeParams', 'APIProducts', 'APICompanies', '$filter', (self, $routeParams, APIProducts, APICompanies, $filter) ->
    APIProducts.query {}, (data) ->
      forfilter = data.sort ->
        return 0.5 - Math.random()        
      self.products = $filter('limitTo')(forfilter, 3)

    self.allowslick = false
    self.id = $routeParams.id

    self.n_links = []

    self.product = APIProducts.get {id: self.id}, (data) ->
      if data.links
        links = data.links.split("\n")
        for link in links
          link = link.split("|")
          if link.length == 2
            self.n_links.push
              title: link[0]
              href: link[1]
              type: 'link'

      for i in [1..5]
        if self.product["file#{i}"].length > 0
          self.n_links.push
            title: if self.product["file#{i}text"] then self.product["file#{i}text"] else self.product["file#{i}"][0].file_name
            href: self.product["file#{i}"][0].file_url
            type: 'file'

      self.allowslick = true

    self.slickConfig =
      dots: false
      infinite: true
      enabled: true
      autoplay: false
      slidesToShow: 1
      slidesToScroll: 1
      arrows: true
      useTransform: false
      adaptiveHeight: true
      prevArrow: '<span class="slim-prev btn btn-block btn-outline-primary">‹</span>'
      nextArrow: '<span class="slim-next btn btn-block btn-outline-primary">›</span>'

  ])