import React, { useState, useEffect } from "react";

const FetchAddon = ({ requestType, postOveride = null }) => {
//   const [formValues, setFormValues] = useState({
//     "user-option": "fetch-media",
//   });
  const [increment, setIncrement] = useState(0);
  const [dataSync, setDataSync] = useState("");
  // const [dataSyncProgress, setDataSyncProgress] = useState('');
  // Check if there is an element with data-sync="valid"
  const syncIsRunning = document.querySelector('[data-sync="valid"]');
  // Determine if the button should be disabled
  const isButtonDisabled = syncIsRunning !== null;

  // Handle the state of the loader and initiate the sync process
  useEffect(() => {
    // if (dataSync) {
    //     handleLoaderState(dataSync, dataSyncProgress);
    // }
  }, [dataSync]);

  // Update handleLoaderState to accept and use userOption
  const handleLoaderState = (userOption) => {
    const f_wpwrap = document.querySelector("#wpwrap");
    const f_wpcontent = document.querySelector("#wpcontent");

    if (f_wpwrap) {
      f_wpwrap.classList.add("loader");
      f_wpcontent.insertAdjacentHTML(
        "beforebegin",
        `
                <div class="progress-bar"></div>
                <div class="centered-blob">
                    <div class="blob-1"></div>
                    <div class="blob-2"></div>
                </div>
                <div class="page-counter">Please do not refresh the page!</div>
            `
      );
      // Pass the userOption directly to handlePostData
      handlePostData(userOption, "all", increment, "inprogress");
    }
  };

//   // Handle form changes
//   const handleChange = (e) => {
//     setFormValues({ "user-option": e.target.value });
//   };

//   // Handle form submission
//   const handleSubmit = (e) => {
//     e.preventDefault();
//     console.log("clicked submit");

//     handleLoaderState(dataSync);
//   };

  // Perform the POST request
  const handlePostData = async (userOptions, mimeType, increment, sync) => {
    const data = new FormData();
    data.append("action", requestType);
    data.append("nonce", wpVars.nonce);
    data.append("post_overide", postOveride);
    data.append("user_option", userOptions);
    data.append("mime_type", mimeType);
    data.append("increment", increment);
    data.append("sync", sync);

    try {
      const response = await fetch(wpVars.ajaxURL, {
        method: "POST",
        credentials: "same-origin",
        body: data,
      });
      const result = await response.json();
      handleResponse(result);
    } catch (error) {
      console.error("Error:", error);
      // location.reload(); // Consider handling errors more gracefully
    }
  };

  // Handle the server response
  const handleResponse = (data) => {
    // console.log("handleResponse");
    // console.log(data);

    if (!data || !data.data) return;

    const response = data.data["response"];
    switch (response) {
      case "Reload":
        setTimeout(() => {
          alert("Synchronization is complete! Page will auto reload.");
          location.reload();
        }, 50);
        break;
      case "Done":
        setTimeout(() => {
          location.reload();
          setIncrement((prev) => prev + 1);
        }, 50);
        break;
      case "Cleaner-Done":
        setTimeout(() => {
          alert("Media cleanup complete! Page will auto reload.");
          location.reload();
        }, 50);
        break;
      default:
        if (response.includes("Collector-Sync-inprogress")) {
          // setDataSyncProgress(data.data['sync']);
          setDataSync(response);
        } else if (response === "Collector-Sync-done") {
          alert("Sync is completed! Please do not refresh the page!");
          setTimeout(() => setDataSync("DONE"), 500);
        }
    }
  };

  const handleSubmitWithAction = (action) => (e) => {
    e.preventDefault();
    const userOption = action;

    if (userOption === "delete-media") {
      if (!window.confirm("Are you sure you want to bulk delete media?")) {
        return;
      }
    }

    handleLoaderState(userOption);    
  };

  return (
    <div className="media-cleaner-block">
      <div className="media-cleaner-block__inner">
        <div className="media-cleaner-item" id={requestType}>
          <div className="media-cleaner-item__inner">
            <div className="media-cleaner-item__content">
              <div className="media-cleaner-item__actions">
                <button
                  onClick={handleSubmitWithAction("fetch-media")}
                  className={
                    isButtonDisabled
                      ? "submit-btn submit-btn-disabled"
                      : "submit-btn"
                  }
                  disabled={isButtonDisabled}
                >
                  {isButtonDisabled
                    ? "Sync in progress — please wait..."
                    : "Initiate Sync"}
                </button>

                <button
                  onClick={handleSubmitWithAction("delete-media")}
                  className={
                    isButtonDisabled
                      ? "submit-btn submit-btn-disabled delete-btn"
                      : "submit-btn delete-btn"
                  }
                  disabled={isButtonDisabled}
                >
                  {isButtonDisabled
                    ? "Sync in progress — delete unavailable"
                    : "Delete Media"}
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default FetchAddon;
