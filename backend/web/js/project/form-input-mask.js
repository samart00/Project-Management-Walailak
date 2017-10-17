$('#from,#to').inputmask("d/m/y", {
    placeholder: "__/__/____", 
    insertMode: false, 
    showMaskOnHover: true,
  }
);

$('#toTime,#fromTime').inputmask("hh:mm", {
    placeholder: "__:__", 
    insertMode: false, 
    showMaskOnHover: true,
    hourFormat: "24"
  }
);