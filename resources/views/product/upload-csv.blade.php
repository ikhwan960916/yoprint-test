<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width">
  <title>YoPrint | Upload CSV</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <style>
    #wrapper {
      display: flex;
      border: 1px solid black;
      padding: 10px;
      margin-right: 20px;
    }

    #drop_zone {
      flex-grow: 1;
      height: 30px;
      border: 1px dashed #000;
      line-height: 30px;
      text-align: center;
      margin-right: 10px;
    }

    #upload_btn {
      padding: 5px 10px;
      border: 1px solid black;
      background-color: white;
    }

    table {
      border-collapse: collapse;
      width: 100%;
      margin-top: 15px;
    }

    th,
    td {
      border: 1px solid black;
      padding: 8px 15px;
      text-align: left;
    }

    th {
      background-color: #f2f2f2;
    }

    .status-column {
      width: 25%;
    }

    .file-name-column {
      width: 35%;
    }

    .time-column {
      width: 40%;
    }
  </style>
</head>

<body>
    <div id="wrapper">
        <div id="drop_zone">Select file/Drag and drop</div>
        <button id="upload_btn">Upload File</button>
    </div>
    
    <input id="uploadFileInput" type="file" style="visibility: hidden;" multiple>
    
    <table id="fileStatusTable">
        <thead>
            <tr>
              <th class="time-column">Time</th>
              <th class="file-name-column">File Name</th>
              <th class="status-column">Status</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
</body>
<script src="https://js.pusher.com/7.0/pusher.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.11.0/dist/echo.iife.js"></script>
<script src="{{ asset('js/async-queue.js') }}"></script>
<script>

    const queue = new AsyncQueue();
    const dropZone = document.getElementById('drop_zone');
    const uploadBtn = document.getElementById('upload_btn');
    const fileInput = document.getElementById('uploadFileInput');
    let selectedFile;
    let id = 0;

    window.Pusher = Pusher;
    window.Echo = new Echo({
        broadcaster: 'pusher',
        key: 'my-pusher-key',
        forceTLS: false,
        wsHost: window.location.hostname,
        wsPort: 6001,
        disableStats: true,
        auth: {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        }
    });

    const channel = 'file-upload-status.{{auth()->user()->id}}';
    window.Echo.private(channel).listen('FileStatusNotification', e => {
      console.log(JSON.stringify(e));
      addTableRow(e);
    });

    uploadBtn.addEventListener('click', function (e) {
        e.preventDefault();
        fileInput.click();
    });

    fileInput.addEventListener('change', function (e) {
        e.preventDefault();
        onFileSelected(event.target.files);
    });

    dropZone.addEventListener('dragover', function (e) {
        e.preventDefault();
        dropZone.style.background = '#E0E0E0';
    });

    dropZone.addEventListener('dragleave', function () {
        dropZone.style.background = 'white';
    });

    dropZone.addEventListener('drop', function (e) {
        e.preventDefault();
        dropZone.style.background = 'white';

        onFileSelected(e.dataTransfer.files);
    });

    async function onFileSelected(files) {

      let isFileSelected = files.length > 0 && files[0].type === 'text/csv';
      if (!isFileSelected) return;

        for (const file of files) {
            const task1Promise = new Promise(async (resolve, reject) => {

            selectedFile = files[0];
            dropZone.textContent = files[0].name;

            // simulate upload request
            // await new Promise(r => setTimeout(r, 1000));
            const formData = new FormData();
            formData.append('file', file);

            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            try {
                const response = await fetch('../../product/upload-csv', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: formData
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                // const data = await response.json();
                console.log(await response.text());
                resolve();
            } catch (error) {
                console.error(error);
            }
        });

        queue.enqueue(() => task1Promise);
        await new Promise(r => setTimeout(r, 1000));
      }

    }

    function getRandomNumber(min, max) {
        min = Math.ceil(min);
        max = Math.floor(max);
        return Math.floor(Math.random() * (max - min) + min);
    }

    function addTableRow(fileStatusNotification) {
        const table = document.getElementById("fileStatusTable").getElementsByTagName('tbody')[0];

        // Update those with existing id
        const existingRow = document.querySelector(`[data-id="${fileStatusNotification.file_status.file_id}"]`);
        if (existingRow) {
          updateTableRow(fileStatusNotification);
          return;
        }

        // insert new row with data-id attribute
        const newRow = table.insertRow();
        newRow.setAttribute("data-id", fileStatusNotification.file_status.file_id);

        // Time
        const timeCell = newRow.insertCell(0);
        timeCell.textContent = fileStatusNotification.file_status.uploaded_time;

        // File Name
        const fileNameCell = newRow.insertCell(1);
        fileNameCell.textContent = fileStatusNotification.file_status.file_name;

        // Status
        const statusCell = newRow.insertCell(2);
        statusCell.textContent = fileStatusNotification.file_status.file_progress.status;
    }

    function updateTableRow(fileStatusNotification) {
        // Find row with matching data-id attribute
        const rowToUpdate = document.querySelector(`[data-id="${fileStatusNotification.file_status.file_id}"]`);
        if (!rowToUpdate) return;

        rowToUpdate.cells[0].textContent = fileStatusNotification.file_status.uploaded_time;
        rowToUpdate.cells[1].textContent = fileStatusNotification.file_status.file_name;

        let status = fileStatusNotification.file_status.file_progress.status;
        let statusContent = status == 'PROCESSING' ? `${status} (${fileStatusNotification.file_status.file_progress.progress_percentage}%)`: status;
        rowToUpdate.cells[2].textContent = statusContent;
    }

  </script>
</html>