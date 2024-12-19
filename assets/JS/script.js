document.getElementById('ticketForm').addEventListener('submit', function(event) {
    event.preventDefault();

    // Get form values
    const name = document.getElementById('name').value;
    const email = document.getElementById('email').value;
    const subject = document.getElementById('subject').value;
    const description = document.getElementById('description').value;

    // Create a new div for the submitted ticket
    const ticketList = document.getElementById('ticketList');
    const ticketDiv = document.createElement('div');
    ticketDiv.className = 'ticket';

    ticketDiv.innerHTML = `
        <p><strong>Name:</strong> ${name}</p>
        <p><strong>Email:</strong> ${email}</p>
        <p><strong>Subject:</strong> ${subject}</p>
        <p><strong>Description:</strong> ${description}</p>
    `;

    // Append the new ticket to the list of submitted tickets
    ticketList.appendChild(ticketDiv);

    // Clear the form fields after submission
    document.getElementById('ticketForm').reset();
});
