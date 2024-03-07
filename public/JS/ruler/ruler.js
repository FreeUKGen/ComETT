function ruler() {
  const ruler = document.createElement("div");

  ruler.style.cssText = `
position: absolute;
top: ${window.scrollY + window.innerHeight/2 - 20}px;
left: ${window.scrollX + window.innerWidth/2 - 20}px;
width: 40px;
height: 40px;
background: #ff00006e;
outline: none;
z-index: 99999;
  `;

  // D&D
  ruler.draggable = true;

  const dragstartHandler = e => {
    e.dataTransfer.dropEffect = "move";

    const mouseOffset = {
      x: e.offsetX,
      y: e.offsetY,
    };

    e.dataTransfer.setData("text/plain", JSON.stringify(mouseOffset));
  };

  ruler.addEventListener("dragstart", dragstartHandler);

  const dragoverHandler = e => {
    e.preventDefault();
  };

  document.addEventListener("dragover", dragoverHandler);

  const dropHandler = e => {
    e.preventDefault();

    const mouseOffset = JSON.parse(e.dataTransfer.getData("text/plain"));

    ruler.style.left = `${e.pageX - mouseOffset.x}px`;
    ruler.style.top = `${e.pageY - mouseOffset.y}px`;
  };

  document.addEventListener("drop", dropHandler);

  // Info
  const getInfo = () => {
    return `${ruler.clientWidth}x${ruler.clientHeight}`;
  };

  const printInfo = () => console.info(getInfo());

  const updateTitle = () => {
    ruler.title = getInfo();
  };

  // Sizing & position
  const changeSize = (x, y) => {
    const newWidth = ruler.clientWidth + x;
    const newHeight = ruler.clientHeight + y;

    ruler.style.width = `${Math.max(1, newWidth)}px`;
    ruler.style.height = `${Math.max(1, newHeight)}px`;

    updateTitle();
    printInfo();
  };

  const moveTo = (x, y) => {
    ruler.style.left = `${x}px`;
    ruler.style.top = `${y}px`;
  }

  const changePosition = (x, y) => {
    const newLeft = ruler.offsetLeft + x;
    const newTop = ruler.offsetTop + y;

    moveTo(newLeft, newTop);
  };


  // Focus and keyboard
  ruler.tabIndex = 0;
  ruler.focus();

  const clickHandler = e => {
    ruler.focus();
  };

  ruler.addEventListener("click", clickHandler);

  const keydownHandler = e => {
    e.preventDefault();

    const { key, shiftKey, ctrlKey } = e;

    if (["ArrowLeft", "ArrowRight", "ArrowUp", "ArrowDown", "j", "k", "h", "l", "J", "K", "H", "L"].includes(key)) {
      const mul = shiftKey ? 10 : 1;

      const x = ["ArrowLeft", "h", "H"].includes(key) ? -1 : ["ArrowRight", "l", "L"].includes(key) ? 1 : 0;

      const y = ["ArrowUp", "k", "K"].includes(key) ? -1 : ["ArrowDown", "j", "J"].includes(key) ? 1 : 0;

      if (ctrlKey) {
        changeSize(x * mul, y * mul);
      } else {
        changePosition(x * mul, y * mul);
      }
    }

    if (key === "Escape") {
      removeRuler();
    }

    if (key === "i") {
      printInfo();
    }

    if (key === "t") {
      tracking = !tracking;
    }

    if (key === "r") {
      const { width, height } = ruler.style;

      ruler.style.width = height;
      ruler.style.height = width;
    }
  };

  ruler.addEventListener("keydown", keydownHandler);

  // Mouse tracking. See also the document keydown event handler
  let tracking = false;

  const mousemoveHandler = e => {
    if (!tracking) {
      return
    }

    moveTo(e.pageX, e.pageY);
  }

  const stopTracking = () => tracking = false;

  ruler.addEventListener("click", stopTracking);

  document.addEventListener("mousemove", mousemoveHandler);

  document.body.appendChild(ruler);

  updateTitle();

  const removeRuler = () => {
    ruler.removeEventListener("dragstart", dragstartHandler);
    ruler.removeEventListener("click", clickHandler);
    ruler.removeEventListener("keydown", keydownHandler)
    ruler.removeEventListener("click", stopTracking);
    document.removeEventListener("dragover", dragoverHandler);
    document.removeEventListener("drop", dropHandler);
    document.removeEventListener("mousemove", mousemoveHandler);

    ruler.remove();
  };

  return removeRuler;
}

var removeRuler;

if (removeRuler) {
  removeRuler();
}

removeRuler = ruler();
