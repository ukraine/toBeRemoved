// JavaScript Document
function gallery(sm,image){
	for (i=1; i<50; i++){
		if(document.getElementById('sm'+i)) document.getElementById('sm'+i).className='';
		else break;																  
	}	
	document.getElementById('sm'+sm).className='active';	
	if (!image) document.getElementById('prev').src=document.getElementById('sm'+sm).src;
	else {
		document.getElementById('prev').src=image;
	}
	image=false;
}

function openimg(){
	img=document.getElementById('prev').src;
	alert(img)
	window.open(img);
	return false;
}