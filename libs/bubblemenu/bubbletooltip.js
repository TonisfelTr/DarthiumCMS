class BubbleMenu {
    visible = false

    showTooltip(element) {
        let tooltip = element.dataset.tooltip,
            bubble = document.querySelector("#tooltip"),
            margin = element.offsetWidth + 10,
            position = element.getBoundingClientRect()

        bubble.style.display = "block";
        bubble.style.top = position.y + 5 + "px";
        bubble.style.left = margin + "px"
        bubble.innerText = tooltip;

        element.onmouseleave = function () {
            bubble.style.display = "none";
        }

    }

    showMenu(element) {
        let menuBlockId = element.dataset.menuBlock,
            position = element.getBoundingClientRect(),
            menuBlock = document.querySelector("#" + menuBlockId),
            margin = element.offsetWidth,
            panel = element.parentNode

        menuBlock.style.display = "flex";
        menuBlock.style.top = position.y + "px";
        menuBlock.style.left = margin + "px";
        this.visible = true

        panel.childNodes.forEach((_el) => {
            _el.onmouseenter = function(e) {
                if (e.target != element){
                    menuBlock.style.display = "none";
                }
            }
        })

        menuBlock.onmouseleave = function () {
            menuBlock.style.display = "none"
            this.visible = false
        }
    }
}
