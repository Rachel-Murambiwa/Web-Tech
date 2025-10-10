function registerUser(event) {
    event.preventDefault();

    const status = document.getElementById("registerStatus").value;
    alert("Registered successfully as " + status + "!");

    
    if (status === "student") {
      window.location.href = "student.html";
    } else if (status === "faculty") {
      window.location.href = "faculty.html";
    } else if (status === "admin") {
      window.location.href = "admin.html";
    }
  }