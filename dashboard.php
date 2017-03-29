<?php
include("session.php");
?>
<!doctype html>
<html class="no-js" lang="en">
  <head>
    <meta charset="utf-8">
    <meta content="IE=edge,chrome=1" http-equiv="X-UA-Compatible">
    <title>Google Maps | Admin</title>    
    <link href="css/example.css" media="screen" rel="stylesheet" type="text/css" /> 
	<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js" type="text/javascript"></script>
    <script src="http://maps.googleapis.com/maps/api/js?sensor=false&libraries=drawing,places" type="text/javascript"></script>
    <script src="js/markerwithlabel.js" type="text/javascript"></script>
	<script type="text/javascript">

  var overlay, image, selectedShape, 
  polys   = new Array(),
  polylns = new Array(),
  auth    = false,
  map     = null,
  status  = 'Ia',
  // Pick a random lat & lng at the start
  lat     = -6  + Math.floor( Math.random()*5 ),
  lng     = 110 + Math.floor( Math.random()*12 ),
  zoom    = 10; 
 
  
  query = "SELECT * FROM write_shapes";
  
  var rectangle;
  var cityCircle;
  var geocoder;
  var infowindow = new google.maps.InfoWindow();
  var marker;
  function drawRectangle(id, rec)
  {  
		  //alert('>>>>>>'+id);
		  for (i=0;i<rec.length;i++)
		  {			
				var coords = JSON.parse(rec[i].geoj).coordinates[0][0];	
				//alert(coords);		
				//tmp = new google.maps.Rectangle({bounds: bb_(coords),editable: false});   
				
				rectangle = new google.maps.Rectangle({
				strokeColor: '#FF0000',
				strokeOpacity: 0.8,
				strokeWeight: 2,
				fillColor: '#FF6600',
				fillOpacity: 0.35,
				map: map,
				bounds: bb_(coords)
			  });			
			  rectangle.setMap(map);  			
		  }
		 rectangle.gmap_id = id; 
		   /*********************load markers if any********************/
	  var text_url = "gettext.php?id="+id+"&action=gettext";
	   $.getJSON(text_url,function(response) {
	
		 //check if empty or not
		 if(!isEmpty(response)) {
				
				 var name 	= response.mName;		
				 var point 	= new google.maps.LatLng(parseFloat(response.mLat),parseFloat(response.mLng));
				 create_marker_info(point, name,id,false, false, false);
			}
		   
	   });	
	  google.maps.event.addListener(rectangle, 'click', function() {
        this.setEditable(true);		
        setSelection(this);		
      });
	  
	  // Add an event listener on the rectangle.
  	google.maps.event.addListener(rectangle, 'bounds_changed', function(){showNewRect(id)});
	
	google.maps.event.addListener(rectangle, 'rightclick', function(event){            
            create_marker(newPoly,id,event,true,true);        
    }); 

  }
  function bb_(coords) {
      //return new google.maps.LatLngBounds(pp_.apply(this, sw),
      //pp_.apply(this, ne));
	  for (j in coords) {			  	
		var sw = coords[j][0] ;
		var ne = coords[j][1] ;
	  }
	  return new google.maps.LatLngBounds(pp_.apply(this, sw),
      pp_.apply(this, ne));
	
   }
   
    function pp_(lat, lng) {	
      return new google.maps.LatLng(lat, lng);					
   }
   
   function showNewRect(id) {	  
	   var ne = rectangle.getBounds().getNorthEast();
       var sw = rectangle.getBounds().getSouthWest();		
	   storeRectangle(rectangle.getBounds(),id);	
	}

   function drawCircle(id,circ)
   {
	   
	   for (i=0;i<circ.length;i++)
	   {	
	   		var coords = JSON.parse(circ[i].geoj).coordinates[0][0];
			var radius = JSON.parse(circ[i].geoj).radius;
			for (j in coords) {			  	
				var sw = coords[j][0] ;
				var ne = coords[j][1] ;
			}						
			var circleOptions = {
			  strokeColor: '#FF0000',
			  strokeOpacity: 0.8,
			  strokeWeight: 2,
			  fillColor: '#FF6600',
			  fillOpacity: 0.35,
			  map: map,
			  center: pp_(sw,ne),
			  radius: Number(radius)
			};
			cityCircle = new google.maps.Circle(circleOptions);	
			cityCircle.setMap(map);
	   }
	   cityCircle.gmap_id = id; 
	  /*********************load markers if any********************/
	  var text_url = "gettext.php?id="+id+"&action=gettext";
	   $.getJSON(text_url,function(response) {
	
		 //check if empty or not
		 if(!isEmpty(response)) {
				
				 var name 	= response.mName;		
				 var point 	= new google.maps.LatLng(parseFloat(response.mLat),parseFloat(response.mLng));
				 create_marker_info(point, name,id,false, false, false);
			}
		   
	   });	
	  google.maps.event.addListener(cityCircle, 'click', function() {
        this.setEditable(true);		
        setSelection(this);		
      });
	  
	  google.maps.event.addListener(cityCircle, 'radius_changed', function(){showNewCircle(id)});
	  google.maps.event.addListener(cityCircle, 'center_changed', function(){showNewCircle(id)});
	  google.maps.event.addListener(cityCircle, 'rightclick', function(event){            
            create_marker(cityCircle,id,event,true,true);        
      }); 
	  
	  
   }
   function showNewCircle(id)
   {
	  
	   var radius = cityCircle.getRadius();	 
	   var geometry = getGeometry(cityCircle.getCenter());	  
	   storeCircle(radius,geometry,id);
   }
   
   function drawPolyline(id,polylne)
   {
	   var polylnoptions = {
		path: polylne,
		geodesic: true,
		strokeColor: '#FF0000',
		strokeOpacity: 1.0,
		strokeWeight: 2,
		fillColor:'#FF6600',
		fillOpacity: 0.35,
	  };
	
	  newPolyln = new google.maps.Polyline(polylnoptions);
      newPolyln.gmap_id = id;
      newPolyln.setMap(map);
	  
	  var place_polygon_path = newPolyln.getPath();
	  
	  /*********************load markers if any********************/
	  var text_url = "gettext.php?id="+id+"&action=gettext";
	   $.getJSON(text_url,function(response) {
	
		 //check if empty or not
		 if(!isEmpty(response)) {
				
				 var name 	= response.mName;		
				 var point 	= new google.maps.LatLng(parseFloat(response.mLat),parseFloat(response.mLng));
				 create_marker_info(point, name,id,false, false, false);
			}
		   
	   });	  
      google.maps.event.addListener(newPolyln, 'click', function() {
        this.setEditable(true);		
        setSelection(this);		
      });
	  google.maps.event.addListener(place_polygon_path, 'set_at', function() {
    	// complete functions
		//alert('set at');
		storePolyline(place_polygon_path , id);	
  		});

	  google.maps.event.addListener(place_polygon_path, 'insert_at', function() {		  
		// complete functions	
		//alert('insert at');	
		storePolyline(place_polygon_path , id);		
	  });
	  
	  google.maps.event.addListener(newPolyln, 'rightclick', function(event){            
            create_marker(newPolyln,id,event,true,true);        
        });
	
      google.maps.event.addListener(map, 'click', clearSelection);
      polylns.push(newPolyln);   
   }
  
 
  function drawPolygon(id, poly) {
    // Construct the polygon
    // Note that we don't specify an array or arrays, but instead just
    // a simple array of LatLngs in the paths property
	//alert('draw polgon');
    var options = { paths: poly,
      strokeColor: '#AA2143',
      strokeOpacity: 1,
      strokeWeight: 2,
      fillColor: "#FF6600",
      fillOpacity: 0.35 };

      newPoly = new google.maps.Polygon(options);
      newPoly.gmap_id = id;
      newPoly.setMap(map);	 
	  
	  var place_polygon_path = newPoly.getPath();
	  
	  /*********************load markers if any********************/
	  var text_url = "gettext.php?id="+id+"&action=gettext";
	   $.getJSON(text_url,function(response) {
	
		 //check if empty or not
		 if(!isEmpty(response)) {
				
				 var name 	= response.mName;		
				 var point 	= new google.maps.LatLng(parseFloat(response.mLat),parseFloat(response.mLng));
				 create_marker_info(point, name,id,false, false, false);
			}
		   
	   });	  
      google.maps.event.addListener(newPoly, 'click', function() {
        this.setEditable(true);		
        setSelection(this);		
      });
	 
	  google.maps.event.addListener(place_polygon_path, 'set_at', function() {
    	// complete functions
		//alert('set at');
		storePolygon(place_polygon_path , id);	
  		});

	  google.maps.event.addListener(place_polygon_path, 'insert_at', function() {		  
		// complete functions	
		//alert('insert at');	
		storePolygon(place_polygon_path , id);		
	  });
	  
	  google.maps.event.addListener(newPoly, 'rightclick', function(event){            
            create_marker(newPoly,id,event,true,true);        
        });
	
      google.maps.event.addListener(map, 'click', clearSelection);
      polys.push(newPoly);
  }
    
 function isEmpty(obj) {
    for(var key in obj) {
        if(obj.hasOwnProperty(key))
            return false;
    }
    return true;
}



function create_marker_info(MapPos, MapTitle,id,InfoOpenDefault, DragAble, Removable)
{
		//alert('create marker info');
	//new marker
		var marker = new MarkerWithLabel({
			position: MapPos,
			map: map,
			draggable:DragAble,
			animation: google.maps.Animation.DROP,
			title:MapTitle,
		   labelContent: MapTitle,
		   labelAnchor: new google.maps.Point(22, 0),
		   labelClass: "labels", // the CSS class for the label
		   labelStyle: {opacity: 0.75},
		   labelVisible: true,
		   icon: "pin_green.png"
		
		});
		
		//Content structure of info Window for the Markers
		var contentString = $('<div class="marker-info-win">'+
		'<div class="marker-inner-win"><span class="info-content">'+		
		'<h1 class="marker-heading">'+MapTitle+'</h1>'+
		'</span><button name="remove-marker" class="remove-marker" title="Remove Marker">Remove Marker</button>'+
		'</div></div>');	

		
		//Create an infoWindow
		var infowindow = new google.maps.InfoWindow();
		//set the content of infoWindow
		infowindow.setContent(contentString[0]);
		//Find remove button in infoWindow
		var removeBtn 	= contentString.find('button.remove-marker')[0];
		//add click listner to remove marker button
		google.maps.event.addDomListener(removeBtn, "click", function(event) {
			remove_marker(marker,id);
		});
		
		//add click listner to save marker button		 
		google.maps.event.addListener(marker, 'click', function() {
				infowindow.open(map,marker); // click on marker opens info window 
	    });		
		
}



//############### Remove Marker Function ##############
	function remove_marker(Marker,id)
	{
			//alert('remove marker');
		/* determine whether marker is draggable 
		new markers are draggable and saved markers are fixed */
	
		if(Marker.getDraggable()) 
		{
			Marker.setMap(null); //just remove new marker
			Marker=null;
		
		}
		else
		{
			//Remove saved marker from DB and map using jQuery Ajax
			
			var myData = {del : 'true', id : id}; //post variables
			$.ajax({
			  type: "POST",
			  url: "gettext.php",
			  data: myData,
			  success:function(data){
					Marker.setMap(null); 
					alert(data);
				},
				error:function (xhr, ajaxOptions, thrownError){
					alert(thrownError); //throw any errors
				}
			});
		}

	}
  
  function create_marker(shape,id,event,DragAble,Removable)
	{	  	  		  
			//alert('create marker');
		//new marker
		var marker = new google.maps.Marker({
			position: event.latLng,
			map: map,
			draggable:true,
			animation: google.maps.Animation.DROP,
			icon: "pin_green.png"
						
		});		
		
		var EditForm = '<p><div class="marker-edit">'+				
				'<label for="pName"><span>Name :</span><input type="text" name="pName_'+shape.appId+'" id="pName_'+shape.appId+'" class="save-name" placeholder="Enter Title" maxlength="40" /></label>'+
				'</div></p><button name="save-marker" class="save-marker">Save</button>';
				
				var contentString = $('<div class="marker-info-win">'+
		'<div class="marker-inner-win"><span class="info-content">'+		
		EditForm+ 
		'</span><button name="remove-marker" class="remove-marker" title="Remove Marker">Remove Marker</button>'+
		'</div></div>');				

				//create info window
                var infoWindow= new google.maps.InfoWindow({
                    content: contentString[0]
                });
              
                infoWindow.setPosition(event.latLng);              
                infoWindow.open(map);				
				var saveBtn = contentString.find('button.save-marker')[0];			
				//add click listner to remove marker button
				/*google.maps.event.addDomListener(removeBtn, "click", function(event) {				
					remove_marker(marker,id);
				});	*/		
								
				//add click listner to save marker button
				google.maps.event.addDomListener(saveBtn, "click", function(event) {
				
					var mReplace = contentString.find('span.info-content'); //html to be replaced after success
					var mName = contentString.find('input.save-name')[0].value; //name input field value				
					if(mName == '')
					{
						alert("Please enter Name!");
					}else{
						
						save_marker(marker,shape,id,mName,mReplace); //call save marker function
					}					
				});	
				//add click listner to save marker button		 
				google.maps.event.addListener(marker, 'click', function() {
						infowindow.open(map,marker); // click on marker opens info window 
				});
				 var InfoOpenDefault = true; 
				if(InfoOpenDefault) //whether info window should be open by default
				{
				  infowindow.open(map,marker);
				}
	}
	
	function save_marker(Marker,shape,id,text,replaceWin)
	{
		//Save new marker using jQuery Ajax
		var mLatLang = Marker.getPosition().toUrlValue(); //get marker position
		var myData = {name : text, id : id, latlang : mLatLang,}; //post variables		
		$.ajax({
		  type: "POST",
		  url: "save_text.php",
		  data: myData,
		  success:function(data){
				//replaceWin.html(data); //replace info window with new html
				replaceWin.html(data); //replace info window with new html
				Marker.setDraggable(false); //set marker to fixed
				Marker.setIcon('pin_green.png'); //replace icon
				//window.location.reload();	
            },
            error:function (xhr, ajaxOptions, thrownError){
                alert(thrownError); //throw any errors
            }
		});
	}

  
    function getShapesData()
  {
	var url = "getdata.php?q="+query;
	$.getJSON(url,function(response) {	 
	  for (i in response.rows) {	
		var 
		coords = JSON.parse(response.rows[i].geoj).coordinates[0][0],
		typeshape = JSON.parse(response.rows[i].geoj).type,	
		poly   = new Array();	
		rec = new Array();
		circ = new Array();
		polylne = new Array();
		//alert(coords);
		if(typeshape == "MultiPolygon")
		{
		
			for (j in coords) {	
						
			  poly.push(new google.maps.LatLng(coords[j][1], coords[j][0]))
			}				
			//poly.pop();
			drawPolygon( response.rows[i].gmap_id, poly);
		}//if polygon
		
		if(typeshape == "Rectangle")
		{
			for (j in coords) {			  	
			  //rec.push(coords[j][1],coords[j][0]);	
			  rec.push(response.rows[i]);	 	  
			}
		    drawRectangle( response.rows[i].gmap_id, rec);	
		}//if rectangle
		if(typeshape == "Circle")
		{
			for (j in coords) {			  	
			  //rec.push(coords[j][1],coords[j][0]);	
			 
			 circ.push(response.rows[i]);	 	  
			}
			drawCircle( response.rows[i].gmap_id, circ);	
			
		}//if circle
		if(typeshape == "Polyline")
		{			
			
			for (j in coords) {			
			  polylne.push(new google.maps.LatLng(coords[j][1], coords[j][0]))
			}				
			//poly.pop();
			drawPolyline( response.rows[i].gmap_id,polylne);
		}//if polyline		
		
	  };
	});
	  
  }

  function clearSelection() {
	//  	alert('clear selection');
    if (!selectedShape) return;
    selectedShape.setEditable(false);
    selectedShape = null;
  }

  function setSelection(shape) {
	//alert('set selection');
    clearSelection();
    selectedShape = shape;
    shape.setEditable(true);
	//alert(shape.gmap_id);
	
	var del_btn = document.getElementById("delete-button");
	    google.maps.event.addDomListener(del_btn,'click',function(event){
            onDeleteButtonClicked();
		});

  }
  
  function onDeleteButtonClicked() {
        //alert("delete button clicked>>>"+selectedShape.gmap_id);
		if (selectedShape != null) {             
           //selectedShape.setMap(null);
		   var id = selectedShape.gmap_id;
		   var myData = {id : id, action : 'delete'}; //post variables		
			$.ajax({
			  type: "POST",
			  url: "delete_shape.php",
			  data: myData,
			  success:function(response){			
					window.location.reload();	
				},
				error:function (xhr, ajaxOptions, thrownError){
					alert(thrownError); //throw any errors
				}
			});
			}
    } 

  function storePolygon(path, gmap_id) {
	//alert('store polygon');	
    var 
    coords  = new Array(),
    payload = { type: "MultiPolygon", coordinates: new Array()};
	
    payload.coordinates.push(new Array());
    payload.coordinates[0].push(new Array());

    for (var i = 0; i < path.length; i++) {
      coord = path.getAt(i);
      coords.push( coord.lng() + " " + coord.lat() );
      payload.coordinates[0][0].push([coord.lng(),coord.lat()])
    }

    var q = "geojson=" + JSON.stringify(payload);
	var ajaxResponse;
    if (gmap_id) {
	  q = q + "&gmap_id=" + gmap_id;
	}
	 $.ajax({
      type: "POST",
      url: "savedata.php",
      data: q,
	  async: false,	
      success:function(response){
          //window.location.reload();	
		ajaxResponse = response;
        },
        error:function (xhr, ajaxOptions, thrownError){
            alert(thrownError); //throw any errors
        }
    });		
	return ajaxResponse;
  }
  
  function storeRectangle(bounds, gmap_id) {
	//alert('store rectangle');	
    var 
    coords  = new Array(),
    payload = { type: "Rectangle", coordinates: new Array()};
	
    payload.coordinates.push(new Array());
    payload.coordinates[0].push(new Array());   
	var geometry = getBounds(bounds);									
    payload.coordinates[0][0].push(geometry) 
    var q = "geojson=" + JSON.stringify(payload);	
	var ajaxResponse;
    if (gmap_id) {
	  q = q + "&gmap_id=" + gmap_id;
	}
	 $.ajax({
      type: "POST",
      url: "savedata.php",
      data: q,
	  async: false,	
      success:function(response){
          //window.location.reload();	
		ajaxResponse = response;
        },
        error:function (xhr, ajaxOptions, thrownError){
            alert(thrownError); //throw any errors
        }
    });		
	return ajaxResponse;
  }
  function storeCircle(radius,geometry,gmap_id)
  {
	  //alert('store circle');
	  
	var 
    coords  = new Array(),
    payload = { type: "Circle", coordinates: new Array(),radius: radius};
	
    payload.coordinates.push(new Array());
    payload.coordinates[0].push(new Array());   
	var geometry = geometry;									
    payload.coordinates[0][0].push(geometry) 
    var q = "geojson=" + JSON.stringify(payload);	
	var ajaxResponse;
    if (gmap_id) {
	  q = q + "&gmap_id=" + gmap_id;
	}
	 $.ajax({
      type: "POST",
      url: "savedata.php",
      data: q,
	  async: false,	
      success:function(response){
          //window.location.reload();		
		ajaxResponse = response;
        },
        error:function (xhr, ajaxOptions, thrownError){
            alert(thrownError); //throw any errors
        }
    });		
	return ajaxResponse;	  
  }
  function storePolyline(path, gmap_id)
  {
	 // alert('store polyline');
	var 
    coords  = new Array(),
    payload = { type: "Polyline", coordinates: new Array()};
	
    payload.coordinates.push(new Array());
    payload.coordinates[0].push(new Array());   
	for (var i = 0; i < path.length; i++) {
      coord = path.getAt(i);
      coords.push( coord.lng() + " " + coord.lat() );
      payload.coordinates[0][0].push([coord.lng(),coord.lat()])
    }

    var q = "geojson=" + JSON.stringify(payload);
	var ajaxResponse;
    if (gmap_id) {
	  q = q + "&gmap_id=" + gmap_id;
	}
	 $.ajax({
      type: "POST",
      url: "savedata.php",
      data: q,
	  async: false,	
      success:function(response){
          //window.location.reload();	
		ajaxResponse = response;
        },
        error:function (xhr, ajaxOptions, thrownError){
            alert(thrownError); //throw any errors
        }
    });		
	return ajaxResponse;

  }
  
   function getBounds(bounds)
    {
        return([getGeometry(bounds.getSouthWest()),
                        getGeometry(bounds.getNorthEast())]);
    }
   function getGeometry(latLng)
  {       
		return([latLng.lat(), latLng.lng()]);
  }
   function getShapePath(path, e) {
        path = (path.getArray) ? path.getArray() : path;
        if (e) {
            return google.maps.geometry.encoding.encodePath(path);
        } else {
            var r = [];
            for (var i = 0; i < path.length; ++i) {
                r.push(getGeometry(path[i]));
            }
            return r;
        }
   }
   
  function codeLatLng(latlngs) {
  var input = latlngs;
  var latlngStr = input.split(',', 2);
  var lat = parseFloat(latlngStr[0]);
  var lng = parseFloat(latlngStr[1]);
  var latlng = new google.maps.LatLng(lat, lng);
  geocoder.geocode({'latLng': latlng}, function(results, status) {
    if (status == google.maps.GeocoderStatus.OK) {
      if (results[1]) {
		var address = results[0].address_components;
		var zipcode = address[address.length - 1].long_name;
		//alert(zipcode);
        map.setZoom(11);
        marker = new google.maps.Marker({
            position: latlng,
            map: map
        });
        infowindow.setContent(results[1].formatted_address);
        infowindow.open(map, marker);
      } else {
        alert('No results found');
      }
    } else {
      alert('Geocoder failed due to: ' + status);
    }
  });
}
   
  

  $(function() {

    //Basic
	//var center = new google.maps.LatLng(26.900831200000000000, 76.353712299999980000);
	var center = new google.maps.LatLng(-31.9688836,115.9313409);
	geocoder = new google.maps.Geocoder();
	var markers = [];
    var cartodbMapOptions = {  
	zoom: zoom,
	center: center,
	//zoom: 17, //zoom level, 0 = earth view to higher value
        panControl: true, //enable pan Control
        zoomControl: true, //enable zoom control
        zoomControlOptions: {
        style: google.maps.ZoomControlStyle.SMALL //zoom control size
        },
        scaleControl: true, // enable scale control
	/*zoom: zoom,
      center: new google.maps.LatLng( lat, lng ),*/
	mapTypeId: google.maps.MapTypeId.ROADMAP,
	disableDefaultUI: true,
	//zoomControl: true	  
    }

    // Init the map
    map = new google.maps.Map(document.getElementById("map"),cartodbMapOptions);
	/*var defaultBounds = new google.maps.LatLngBounds(
      new google.maps.LatLng(-33.8902, 151.1759),
      new google.maps.LatLng(-33.8474, 151.2631));
    map.fitBounds(defaultBounds);	*/
	
	// Create the search box and link it to the UI element.
	  var input = /** @type {HTMLInputElement} */(
		  document.getElementById('pac-input'));
	  map.controls[google.maps.ControlPosition.TOP_RIGHT].push(input);
	
	  var searchBox = new google.maps.places.SearchBox(
		/** @type {HTMLInputElement} */(input));
		
		
	// Listen for the event fired when the user selects an item from the
	  // pick list. Retrieve the matching places for that item.
	  google.maps.event.addListener(searchBox, 'places_changed', function() {
		var places = searchBox.getPlaces();
		
		for (var i = 0, marker; marker = markers[i]; i++) {
		  marker.setMap(null);
		}
		// For each place, get the icon, place name, and location.
		markers = [];
		var bounds = new google.maps.LatLngBounds();
		
		for (var i = 0, place; place = places[i]; i++) {
		  var image = {
			url: place.icon,
			size: new google.maps.Size(71, 71),
			origin: new google.maps.Point(0, 0),
			anchor: new google.maps.Point(17, 34),
			scaledSize: new google.maps.Size(25, 25)
		  };
		
		var str = place.geometry.location;		
		var newString = str.toString();	
		var result = newString.substr(1,newString.length-2);		
		codeLatLng(result);
		 // Create a marker for each place.
		  /*var marker = new google.maps.Marker({
			map: map,
			icon: image,
			title: place.name,
			position: place.geometry.location
		  });
	
		  markers.push(marker);*/
	
		  bounds.extend(place.geometry.location);
		}
	
		map.fitBounds(bounds);		
	
	  });	
	getShapesData();
    //getPolygons();
	//getRectangles();
	//getShapes();	
	var RECTANGLE = google.maps.drawing.OverlayType.RECTANGLE;
    var CIRCLE = google.maps.drawing.OverlayType.CIRCLE;
    var POLYGON = google.maps.drawing.OverlayType.POLYGON;
    var POLYLINE = google.maps.drawing.OverlayType.POLYLINE;
    var MARKER = google.maps.drawing.OverlayType.MARKER;
	
	 var drawingModes = new Array(
            RECTANGLE, CIRCLE, POLYGON, POLYLINE);
	 var drawingControlOptions = {
            drawingModes: drawingModes,
            position: google.maps.ControlPosition.TOP_LEFT
        };
	 var polyOptions = {
            editable: true,
            clickable: true        
        };  
	 drawingManagerOptions = {
            drawingMode: null,
            drawingControlOptions: drawingControlOptions,
            markerOptions: { draggable: true },
            polylineOptions: { editable: true , clickable: true},
            rectangleOptions: polyOptions,
            circleOptions: polyOptions,
            polygonOptions: polyOptions,
            map: map
        };
 
        drawingManager = new google.maps.drawing.DrawingManager(
            drawingManagerOptions);

    drawingManager.setMap(map);

    google.maps.event.addListener(drawingManager, 'overlaycomplete', function(e) {
      // Add an event listener that selects the newly-drawn shape when the user
      // mouses down on it.
      var newShape = e.overlay;
      newShape.type = e.type;
	  
	   google.maps.event.addListener(newShape, 'click', function() {		
        setSelection(this);
      });

      setSelection(newShape);
	  
	  if(newShape.type == "rectangle")
	  {
		  //alert('innnnnn rectangle');
		  var newshape_id = storeRectangle(newShape.getBounds());
		  var newshape_path = newShape.getBounds();
		  newShape.gmap_id = newshape_id;
	  }//rectangle over
	   if(newShape.type == "polygon")
	   {
		  //alert('innnn polygon');
		   //storePolygon(newShape.getPath());	  
		  var newshape_id = storePolygon(newShape.getPath());
		  var newshape_path = newShape.getPath();
		  newShape.gmap_id = newshape_id;
		 
		   google.maps.event.addListener(newshape_path, 'set_at', function() {
			//alert('main set at');
			storePolygon(newshape_path , newshape_id);	
			});
	
		  google.maps.event.addListener(newshape_path, 'insert_at', function() {
			//alert('main insert at');	
			storePolygon(newshape_path , newshape_id);		
		  });		  
	  } //polygon over
	   if(newShape.type == "circle")
	   {
		  var radius = newShape.getRadius();                       
          var geometry = getGeometry(newShape.getCenter());		
		  var newshape_id = storeCircle(radius,geometry);
		  newShape.gmap_id = newshape_id;
	   }
	    if(newShape.type == "polyline")
		{
			//alert('in polyline');
			//var geometry = getShapePath(newShape.getPath()); 
			var newshape_id = storePolyline(newShape.getPath());			
		    newShape.gmap_id = newshape_id; 
			var newshape_path = newShape.getPath();
		    google.maps.event.addListener(newshape_path, 'set_at', function() {
			//alert('main set at');
			storePolyline(newshape_path , newshape_id);	
			});
	
		   google.maps.event.addListener(newshape_path, 'insert_at', function() {
			//alert('main insert at');	
			storePolyline(newshape_path , newshape_id);		
		  });		  
			
		}	
	  
	  	google.maps.event.addListener(newShape, 'click', function() {
			this.setEditable(true);		
			setSelection(this);		
		  });	  
	  
	   google.maps.event.addListener(newShape, 'rightclick', function(event){            
            create_marker(newShape,newshape_id,event,true,true);        
        }); 
		
		//add click listner to save marker button		 
		google.maps.event.addListener(marker, 'click', function() {
			alert('innnnnnnnnnnnn');
				infowindow.open(map,marker); // click on marker opens info window 
	    });
		
      newShape.setEditable(false); 	  
    });

  });

</script>
 </head>
  <body>
<h4 align="right">  click here to <a href="logout.php">LogOut</a></h4>
<input id="pac-input" class="controls" type="text" placeholder="Search Box">
<div id="map" style="width:100%; height:80%"></div>
<input type="button" id="delete-button" value="Delete Shape">    
  </body>
</html>
