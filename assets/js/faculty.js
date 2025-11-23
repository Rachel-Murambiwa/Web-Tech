function generatePin() {
      const pin = Math.floor(100000 + Math.random() * 900000);
      document.getElementById('generatedPin').textContent = pin;
      
      setTimeout(() => {
        alert('PIN will expire in 5 minutes');
      }, 500);
    }

    function createCourse(event) {
      event.preventDefault();
      
      const courseCode = document.getElementById('courseCode').value;
      const courseName = document.getElementById('courseName').value;
      const courseDescription = document.getElementById('courseDescription').value;
      const semester = document.getElementById('semester').value;

      const courseList = document.getElementById('courseList');
      const newCourse = document.createElement('li');
      newCourse.className = 'course-item';
      newCourse.innerHTML = `
        <h4>${courseCode} - ${courseName}</h4>
        <p><strong>Semester:</strong> ${semester}</p>
        <p><strong>Students:</strong> 0</p>
      `;
      
      courseList.appendChild(newCourse);
      
      document.getElementById('courseForm').reset();
      alert(`Course "${courseCode} - ${courseName}" created successfully!`);
    }

    function approveRequest(button, studentName) {
      const requestItem = button.closest('.request-item');
      
      if (confirm(`Approve ${studentName}'s course request?`)) {
        requestItem.style.backgroundColor = '#e8f5e9';
        requestItem.style.borderLeftColor = '#4CAF50';
        
        setTimeout(() => {
          requestItem.remove();
          checkEmptyRequests();
        }, 1000);
        
        alert(`${studentName} has been approved and added to the course.`);
      }
    }

    function rejectRequest(button, studentName) {
      const requestItem = button.closest('.request-item');
      
      if (confirm(`Reject ${studentName}'s course request?`)) {
        requestItem.style.backgroundColor = '#ffebee';
        requestItem.style.borderLeftColor = '#f44336';
        
        setTimeout(() => {
          requestItem.remove();
          checkEmptyRequests();
        }, 1000);
        
        alert(`${studentName}'s request has been rejected.`);
      }
    }

    function checkEmptyRequests() {
      const requestsList = document.getElementById('requestsList');
      if (requestsList.children.length === 0) {
        requestsList.innerHTML = '<div class="empty-state">No pending requests</div>';
      }
    }