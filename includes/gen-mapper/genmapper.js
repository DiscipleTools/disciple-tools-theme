// GenMapper
// App for mapping generations of simple churches
// https://github.com/dvopalecky/gen-mapper
// Copyright (c) 2016 Daniel Vopalecky, MIT license

var margin = {top: 30, right: 30, bottom: 50, left: 30},
    height = 800 - margin.top - margin.bottom;
var boxHeight = 80;
var textHeight = 14;
var textMargin = 6;

var zoom = d3.zoom()
  .scaleExtent([0.15, 2])
  .on("zoom", zoomed);  

var projectName = "Untitled project";

d3.select("#project-name")
  .text(projectName)
  .on("click", function() {
    var userInput = prompt("Edit Project name", projectName).trim();
    if(userInput === "")
      displayAlert("Project name can't be empty!");
    else {
      projectName = userInput;
      d3.select("#project-name").text(projectName);  
    }
  });

var svg = d3.select("#main-svg")
      .attr("height", height + margin.top + margin.bottom)
      .call(zoom);  
var g = svg.append("g")
  .attr("class", "maingroup");
var gLinks = g.append("g")
  .attr("class", "group-links");
var gLinksText = g.append("g")
  .attr("class", "group-links-text");
var gNodes = g.append("g")
  .attr("class", "group-nodes");

var csvHeader = "id,parentId,name,coach,field1,field2,field3,field4,field5,placeDate,active";
var initialCsv = csvHeader + "\n0,,Name,Coach,0,0,0,No,0,\"Place, Date\",TRUE";
var data = parseCsvData(initialCsv);
var nodes;

origPosition();
redraw();

var alertElement = document.getElementById("alert-message");
var editGroupElement = document.getElementById("edit-group");
var editNameElement = document.getElementById("edit-name");
var editParentElement = document.getElementById("edit-parent");
var editCoachElement = document.getElementById("edit-coach");
var editField1Element = document.getElementById("edit-field1");
var editField2Element = document.getElementById("edit-field2");
var editField3Element = document.getElementById("edit-field3");
var editField4Element = document.getElementById("edit-field4");
var editField5Element = document.getElementById("edit-field5");
var editPlaceAndDateElement = document.getElementById("edit-place-and-date");
var editActiveElement = document.getElementById("edit-active");

document.addEventListener('keyup', function(e) {
    if (e.keyCode == 27) {
      if (document.getElementById("alert-message").style.display != "none") {
        document.getElementById("alert-message").style.display = "none";
      } else{
        if (document.getElementById("intro").style.display != "none") {
          document.getElementById("intro").style.display = "none";
        }
        if (editGroupElement.style.display != "none") {
          editGroupElement.style.display = "none";
        }  
      }
    }
});

function zoomed() {
  g.attr("transform", d3.event.transform);
}

function zoomIn() {
  zoom.scaleBy(svg, 1.2);
}

function zoomOut() {
  zoom.scaleBy(svg, 1 / 1.2);
}

function origPosition() {
  zoom.scaleTo(svg, 1);  
  var origX = margin.left + (document.getElementById("main-svg").clientWidth / 2);
  var origY = margin.top;
  var parsedTransform = parseTransform(g.attr("transform"));
  zoom.translateBy(svg, origX - parsedTransform.translate[0], origY - parsedTransform.translate[1]);  
}

function onLoad() {
    document.getElementById("file-input").click();
}

function displayAlert(message){
  alertElement.style.display = "block";
  document.getElementById("alert-message-text").innerHTML = message;  
}

function closeAlert() {
  alertElement.style.display = null;
  document.getElementById("alert-message-text").innerHTML = null;    
}    

function introSwitchVisibility(){
  var tmp = d3.select("#intro");
  if (tmp.style("display")!="none")
    tmp.style("display", "none");
  else
    tmp.style("display", "block");
}

function popupEditGroupModal(d) {
  editGroupElement.style.display = "block";
  editNameElement.value = d.data.name;
  editNameElement.select();
  editParentElement.innerHTML = d.parent ? d.parent.data.name : "N/A";  
  editCoachElement.value = d.data.coach;  
  editField1Element.value = d.data.fields[0];  
  editField2Element.value = d.data.fields[1];  
  editField3Element.value = d.data.fields[2];  
  editField4Element.value = d.data.fields[3];  
  editField5Element.value = d.data.fields[4];
  editPlaceAndDateElement.value = d.data.footer;
  editActiveElement.checked = d.data.active;    
  var groupData = d.data;
  var group = d;
  d3.select("#edit-submit").on("click", function() {editGroup(groupData);});
  d3.select("#edit-cancel").on("click", function() {
    editGroupElement.style.display = "none";
  });
  d3.select("#edit-delete").on("click", function() {removeNode(group);});
}

function editGroup(groupData) {
  groupData.name = editNameElement.value;
  groupData.coach = editCoachElement.value;  
  groupData.fields[0] = editField1Element.value;  
  groupData.fields[1] = editField2Element.value;  
  groupData.fields[2] = editField3Element.value;  
  groupData.fields[3] = editField4Element.value;  
  groupData.fields[4] = editField5Element.value;
  groupData.footer = editPlaceAndDateElement.value;
  groupData.active = editActiveElement.checked; 

  editGroupElement.style.display = "none";
  redraw();
}

function printMap(printType) {
  // calculate width and height of the map (printed rotated by 90 degrees)
  var arrNodes = nodes.descendants();
  var x, y;
  var minX = 0, maxX = 0, minY = 0, maxY = 0;
  for (var i = 0; i < arrNodes.length; i++){
    x = arrNodes[i].x;
    y = arrNodes[i].y;
    minX = Math.min(minX, x);
    maxX = Math.max(maxX, x);
    minY = Math.min(minY, y);
    maxY = Math.max(maxY, y);
  }
  
  // store original values
  var origWidth = svg.attr("width");
  var origHeight = svg.attr("height");
  var origTransform = g.attr("transform");
  
  var totalHeight = Math.max(600, margin.top + (maxY - minY) + boxHeight + margin.top);
  var totalWidthLeft = Math.max(500, - minX + boxHeight * 1.5 / 2 + 20);
  var totalWidthRight = Math.max(500,maxX + boxHeight * 1.5 / 2 + 20);
    
  var translateX, translateY;
  if (printType == "horizontal"){
    var printHeight = 700;
    var printWidth = 1200;
    
    // resize for printing
    svg.attr("width", printWidth)
      .attr("height", printHeight);
    var printScale = Math.min(1, printWidth / (totalWidthLeft + totalWidthRight), printHeight / totalHeight);
    translateX = totalWidthLeft * printScale;
    translateY = margin.top * printScale;
    x = "translate(" + translateX + ", " + translateY + ") scale(" + printScale + "))";
    g.attr("transform", "translate(" + translateX + ", " + translateY + ") scale(" + printScale + ")");    
  }
  else {
    
    // resize for printing
    svg.attr("width", totalHeight)
      .attr("height", totalWidthLeft + totalWidthRight);
    translateX = totalHeight - margin.top;
    translateY = totalWidthLeft;
    g.attr("transform", "translate(" + translateX + ", " + translateY + ") rotate(90)");    
  }
  
  // change CSS for printing
  d3.select("#left-menu").style("display", "none");
  d3.select("#main").style("float", "left");
  d3.selectAll("#main-svg").style("background", "white");
  
  window.print();
    
  // change CSS back after printing
  svg.attr("width", origWidth)
    .attr("height", origHeight);
  g.attr("transform", origTransform);
  d3.select("#left-menu").style("display", null);
  d3.select("#main").style("float", null);
  d3.selectAll("#main-svg").style("background", null);     
}

function redraw(){
    // declares a tree layout and assigns the size
    var tree = d3.tree()
      .nodeSize([boxHeight * 1.5, boxHeight * 2])
      .separation(function separation(a, b) {
        return a.parent == b.parent ? 1 : 1.2;
      });
      
    var stratifiedData = d3.stratify()(data);
    nodes = tree(stratifiedData);

    // update the links between the nodes
    var link = gLinks.selectAll(".link")
        .data( nodes.descendants().slice(1));
    
    link.exit()
      .remove();
    
    link.enter()
      .append("path")
    .merge(link)
        .attr("class", "link")
        .attr("d", function(d) {
           return "M" + d.x + "," + d.y +
             "C" + d.x + "," + (d.y + (d.parent.y + boxHeight)) / 2 +
             " " + d.parent.x + "," +  (d.y + (d.parent.y + boxHeight)) / 2 +
             " " + d.parent.x + "," + (d.parent.y + boxHeight);
           });

    // update the link text between the nodes
    var LINK_TEXT_POSITION = 0.3; // 1 -> parent, 0 -> child
    var linkText = gLinksText.selectAll(".link-text")
        .data( nodes.descendants().slice(1));
    linkText.exit()
      .remove();
    linkText.enter()
      .append("text")
    .merge(linkText)
      .attr("class", function(d) { 
        return "link-text " + (d.data.active ? " link-text--active" : " link-text--inactive"); })
      .attr("x", function(d) { return d.x * (1 - LINK_TEXT_POSITION) + d.parent.x * LINK_TEXT_POSITION;})
      .attr("y", function(d) { return d.y * (1 - LINK_TEXT_POSITION) + (d.parent.y + boxHeight) * LINK_TEXT_POSITION;})
      .text(function(d) { return d.data.coach; });

    // update nodes
    var node = gNodes.selectAll(".node")
        .data(nodes.descendants());
    
    node.exit()
      .remove();
    
    // update
    node.attr("class", function(d) { 
          return "node" + (d.data.active ? " node--active" : " node--inactive"); })
        .attr("transform", function(d) { 
          return "translate(" + d.x + "," + d.y + ")"; })
        .on("click", function(d) {popupEditGroupModal(d);});
        
    node.select("text")
      .text(function(d) { return d.data.name; });

    node.select(".removeNode")
      .on("click", function(d) {removeNode(d);event.cancelBubble=true;}); 

    node.select(".addNode")
      .on("click", function(d) {addNode(d);event.cancelBubble=true;});

    node.select(".field1")
      .text(function(d) { return d.data.fields[0]; });
    node.select(".field2")
      .text(function(d) { return d.data.fields[1]; });
    node.select(".field3")
      .text(function(d) { return d.data.fields[2]; });
    node.select(".field4")
      .text(function(d) { return d.data.fields[3]; });
    node.select(".field5")
      .text(function(d) { return d.data.fields[4]; });
    node.select(".box-footer")
      .text(function(d) { return d.data.footer; });      
    
    // NEW ELEMENTS
    var group = node.enter()
        .append("g")
        .attr("class", function(d) { 
          return "node" + (d.data.active ? " node--active" : " node--inactive"); })
        .attr("transform", function(d) { 
          return "translate(" + d.x + "," + d.y + ")"; })
        .on("click", function(d) {popupEditGroupModal(d);});
    
    group.append("title").text("Edit group");  
    gRemoveNode = group.append("g")        
      .attr("class", "removeNode")
      .on("click", function(d) {removeNode(d);event.cancelBubble=true;});
    gRemoveNode.append("rect")
      .attr("x", boxHeight / 2)
      .attr("y", 0)
      .attr("rx", 7)
      .attr("width", 25)
      .attr("height", boxHeight / 2)
      .append("title").text("Remove group");
    gRemoveNode.append("line") // top-left diagonal in X sign
      .attr("x1", boxHeight / 2 + 6)
      .attr("y1", boxHeight * 0.25 - 6.5)
      .attr("x2", boxHeight / 2 + 19)
      .attr("y2", boxHeight * 0.25 + 6.5)
      .attr("stroke", "white")
      .attr("stroke-width", 3);      
    gRemoveNode.append("line") // top-right diagonal in X sign
      .attr("x1", boxHeight / 2 + 19)
      .attr("y1", boxHeight * 0.25 - 6.5)
      .attr("x2", boxHeight / 2 + 6)
      .attr("y2", boxHeight * 0.25 + 6.5)
      .attr("stroke", "white")
      .attr("stroke-width", 3);   
    gAddNode = group.append("g")        
      .attr("class", "addNode")
      .on("click", function(d) {addNode(d);event.cancelBubble=true;});
    gAddNode.append("rect")  
      .attr("x", boxHeight / 2)
      .attr("y", boxHeight / 2)
      .attr("rx", 7)
      .attr("width", 25)
      .attr("height", boxHeight / 2)
      .append("title").text("Add child");
    gAddNode.append("line") // horizontal in plus sign
      .attr("x1", boxHeight / 2 + 5)
      .attr("y1", boxHeight * 0.75)
      .attr("x2", boxHeight / 2 + 20)
      .attr("y2", boxHeight * 0.75)
      .attr("stroke", "white")
      .attr("stroke-width", 3);
    gAddNode.append("line") // vertical in plus sign
      .attr("x1", boxHeight / 2 + 12.5)
      .attr("y1", boxHeight * 0.75 - 7.5)
      .attr("x2", boxHeight / 2 + 12.5)
      .attr("y2", boxHeight * 0.75 + 7.5)
      .attr("stroke", "white")
      .attr("stroke-width", 3); 
    group.append("text")
      .attr("y", -textMargin)
      .text(function(d) { return d.data.name; }); 
    group.append("rect") // field 1
      .attr("x", - boxHeight / 2)
      .attr("y", "0")
      .attr("rx", boxHeight * 0.1)
      .attr("width", boxHeight)
      .attr("height", boxHeight);
    group.append("line")
      .attr("x1", "0")
      .attr("y1", "0")
      .attr("x2", "0")
      .attr("y2", boxHeight);
    group.append("line")
      .attr("x1", - boxHeight / 2)
      .attr("y1", boxHeight / 2)
      .attr("x2", boxHeight / 2)
      .attr("y2", boxHeight / 2);
    group.append("rect") // field 5
      .attr("x", - boxHeight * 0.22)
      .attr("y", boxHeight * 0.28)
      .attr("width", boxHeight * 0.44)
      .attr("height", boxHeight * 0.44)
      .attr("rx", boxHeight * 0.1);
    group.append("text")
      .attr("x", - boxHeight / 2 + textMargin)
      .attr("y", textHeight + textMargin)
      .attr("class", "field1")
      .style("text-anchor", "start")
      .text(function(d) { return d.data.fields[0]; });
    group.append("text")
      .attr("x", boxHeight / 2 - textMargin)
      .attr("y", textHeight + textMargin)
      .attr("class", "field2")
      .style("text-anchor", "end")
      .text(function(d) { return d.data.fields[1]; });
    group.append("text")
      .attr("x", boxHeight / 2 - textMargin)
      .attr("y", boxHeight - textMargin)
      .attr("class", "field3")
      .style("text-anchor", "end")
      .text(function(d) { return d.data.fields[2]; });
    group.append("text")
      .attr("x", - boxHeight / 2 + textMargin)
      .attr("y", boxHeight - textMargin)
      .attr("class", "field4")
      .style("text-anchor", "start")
      .text(function(d) { return d.data.fields[3]; });
    group.append("text")
      .attr("x", 0)
      .attr("y", boxHeight / 2 + textHeight / 2)
      .attr("class", "field5")
      .text(function(d) { return d.data.fields[4]; });    
    group.append("text")
      .attr("x", 0)
      .attr("y", boxHeight + textHeight + textMargin)
      .attr("class", "box-footer")
      .text(function(d) { return d.data.footer; });    
}    

function addNode(d) {
  // find smallest available new id
  var id = -1;
  var ids = [];
  for (i = 0; i < data.length; i++){
    ids.push(data[i].id);
  }
  ids.sort(function(a, b){return a-b;});
  var i;
  for (i = 0; i < ids.length; i++){
    if(ids[i]!=i){
      id = i;
      break;
    }
  }
  if(id == -1)
    id = i+1;

  data.push({
    "name":"Name",
    "fields": ["0", "0", "0", "No", "0"],
    "footer": "Place, Date",
    "id": id,
    "parentId": d.data.id,
    "coach": "Coach",
    "active": true
  });

  redraw();
}

function removeNode(d){
  if (!d.parent){
    displayAlert("Sorry. Deleting root group is not possible.");
  }
  else if (!d.children){
    if(confirm("Do you really want to delete " + d.data.name + "?")){
      var nodeToDelete = _.where(data, {id: d.data.id});
      if (nodeToDelete){
          data = _.without(data, nodeToDelete[0]);
      }  
    }            
  }
  else {
    displayAlert("Sorry, delete not possible. Please delete all descendant groups first.");
  }
  document.getElementById("edit-group").style.display = "none";
  redraw();        
}

function parseCsvData(csvData){
  return d3.csvParse(csvData, function(d) {
    var parsedId = parseInt(d.id);
    if (parsedId < 0 || isNaN(parsedId))
      throw "id must be integer >= 0.";
    return {
      id: parsedId,
      parentId: d.parentId !== "" ? parseInt(d.parentId) : "",
      name: d.name,
      coach: d.coach,
      fields: [d.field1, d.field2, d.field3, d.field4, d.field5],
      footer: d.placeDate,
      active: d.active.toUpperCase() == "TRUE" ? true : false
    };
  });
}

function outputCsv(){
  var headerRow = "id,parentId,name,coach,field1,field2,field3,field4,field5,placeDate,active\n";
  var out = d3.csvFormatRows(data.map(function(d, i) {
    return [
      d.id,
      d.parentId,
      d.name,
      d.coach,
      d.fields[0],
      d.fields[1],
      d.fields[2],
      d.fields[3],
      d.fields[4],
      d.footer,
      d.active ? "TRUE" : "FALSE"
    ];
  }));
  var blob = new Blob([headerRow + out], {type: "text/csv;charset=utf-8"});
  var saveName = prompt("Save as:", projectName + ".csv");
  if(saveName === null) return;
  saveAs(blob, saveName); 
}

function parseTransform(a)
{
    var b={};
    for (var i in a = a.match(/(\w+\((\-?\d+\.?\d*e?\-?\d*,?)+\))+/g))
    {
        var c = a[i].match(/[\w\.\-]+/g);
        b[c.shift()] = c;
    }
    return b;
}

function importFile() {
    var input, file, fr, filename;

    if (typeof window.FileReader !== 'function') {
        displayAlert("The file API isn't supported on this browser yet.");
        return;
    }
    input = document.getElementById('file-input');
    if (!input) {
        displayAlert("Um, couldn't find the fileinput element.");
    }
    else if (!input.files) {
        displayAlert("This browser doesn't seem to support the 'files' property of file inputs.");
    }
    else if (!input.files[0]) {
        displayAlert("Please select a file");
    }
    else {
        file = input.files[0];
        filename = file.name;
        fr = new FileReader();
        fr.onload = processFile;
        fr.readAsBinaryString(file);            
    }

    function processFile() {
        var regex = /(?:\.([^.]+))?$/;
        var extension = regex.exec(filename)[1];
        var filedata = fr.result;
        var csvString;

        if(extension === "xls" || extension === "xlsx"){
          var workbook = XLSX.read(filedata, {type: 'binary'});
          var worksheet = workbook.Sheets[workbook.SheetNames[0]];
          csvString = XLSX.utils.sheet_to_csv(worksheet);
        } 
        else if(extension === "csv") {
          csvString = filedata;  
        } 
        else {
          displayAlert("Wrong type of file. Please import xls, xlsx or csv files.");
          return;
        }
        
        csvString = csvHeader + "\n" + csvString.substring(csvString.indexOf("\n") + 1); //replace first line with a default one
        
        try {
          var tmpData = parseCsvData(csvString);
          var treeTest = d3.tree();
          var stratifiedDataTest = d3.stratify()(tmpData);
          treeTest(stratifiedDataTest);
          data = tmpData;
          redraw();
        }
        catch(err) {
          if(err == "id must be >= 0."){
            displayAlert("Error when importing file. Group id must be >= 0");  
          }
          else {
            displayAlert("Error when importing file. Please check that the file is in correct format (comma separated values), that the root group has no parent, and that all other relationships make a valid tree.");  
          }
        }
    }
}