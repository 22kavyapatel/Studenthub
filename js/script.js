function loginUser(event){

event.preventDefault();

let role=document.getElementById("role").value;

if(role=="student"){
window.location="student-dashboard.html";
}

else if(role=="teacher"){
window.location="teacher-dashboard.html";
}

else if(role=="admin"){
window.location="admin-dashboard.html";
}

else{
alert("Please select role");
}

}