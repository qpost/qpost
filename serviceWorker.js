self.addEventListener("install",(event) => {
    console.log("install",event);
});

self.addEventListener("active",(event) => {
    console.log("active",event);
});

self.addEventListener("push",(event) => {
    fetch("/scripts/desktopNotifications").then((response) => {
        console.log("response " + response);
    }).catch((error) => {
        console.error("Failed to fetch notifications",error);
    });
});