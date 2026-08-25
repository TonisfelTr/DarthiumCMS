/**
 * Show panel
 *
 * @param divTitle
 * @param title
 */
function showPanel(divTitle, title) {
    let inHeaderTitle = title;
    let headedBlock = $(`div[data-div-title="${divTitle}"]`);

    $("div[data-div-title]").hide();
    if ($(headedBlock).hasClass("hidden")) {
        $(headedBlock).removeClass("hidden");
    }
    $(`div[data-div-title="${divTitle}"]`).show();
    $("#profile-form-name").html(inHeaderTitle);
}

/**
 * Show subpanel in panel with parentDivTitle
 *
 * @param parentDivTitle
 * @param childTitle
 */
function showSubpanel(parentDivTitle, childTitle) {
    let div = $(`div[data-div-title="${parentDivTitle}"] > div[data-subdiv-title=${childTitle}]`);
    let anotherDiv = $(`div[data-div-title=${parentDivTitle}] > div`);
    let defaultShownDiv = $(`div[data-div-default]`);

    anotherDiv.hide();
    if ($(div).hasClass("hidden")) {
        $(div).removeClass("hidden");
    }
    div.show();
    defaultShownDiv.show();
}


function showDescPanel(childTitle, descTitle) {
    let descDiv = $(`div[data-subdiv-title=${childTitle}] > div[data-desc-div=${descTitle}]`);
    let anotherDiv = $(`div[data-subdiv-title=${childTitle}] > div`);

    anotherDiv.hide();
    if ($(descDiv).hasClass("hidden")) {
        $(descDiv).removeClass("hidden");
    }
    descDiv.show();
}
