/* global jQuery:false, List:false */


jQuery(document).ready(function() {
  var myContacts = new List('my-contacts', {
    valueNames: ['name', 'team'],
    page: 30,
    pagination: true,
  });


  // load contacts over ajax strategy
  // setTimeout(()=>{
  //   myContacts.clear()
  //   myContacts.add([
  //     { name: 'Jonny', city:'Stockholm' },
  //     { name: 'Jonas', city:'Berlin' }
  //   ])
  // }, 1000)


});
