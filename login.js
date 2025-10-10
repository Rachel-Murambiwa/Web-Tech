function redirectUser(event) {
      event.preventDefault(); // stop form from refreshing

      const status = document.getElementById("status").value;

      if (status === "student") {
        window.location.href = "student.html";
      } else if (status === "faculty") {
        window.location.href = "faculty.html";
      } else if (status === "admin") {
        window.location.href = "admin.html";
      }
    }