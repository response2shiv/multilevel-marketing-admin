<style type="text/css">
    span.inner {
        color: white;
    }

    span.outer {
        color: red;
        text-decoration: line-through;
    }

    span.req {
        color: red;
    }

    /*body {*/
    /*font-family: 'Poppins', sans-serif;*/
    /*margin: 0px;*/
    /*padding: 0px;*/
    /*color: #5a5a5a;*/
    /*}*/

    ul, li, p {
        margin: 0;
        padding: 0
    }

    * {
        box-sizing: border-box;
    }

    .blocker {
        position: fixed;
        top: 0;
        right: 0;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        z-index: 1;
        padding: 20px;
        box-sizing: border-box;
        background-color: rgb(0, 0, 0);
        background-color: rgba(0, 0, 0, 0.75);
        text-align: center;
    }

    .blocker:before {
        content: "";
        display: inline-block;
        height: 100%;
        vertical-align: middle;
        margin-right: -0.05em;
    }

    .blocker.behind {
        background-color: transparent;
    }

    /*.modal {*/
    /*display: inline-block;*/
    /*vertical-align: middle;*/
    /*position: relative;*/
    /*z-index: 2;*/
    /*max-width: 900px;*/
    /*box-sizing: border-box;*/
    /*width: 90%;*/
    /*background: #4aafd1;*/
    /*padding: 0;*/
    /*-webkit-border-radius: 8px;*/
    /*-moz-border-radius: 8px;*/
    /*-o-border-radius: 8px;*/
    /*-ms-border-radius: 8px;*/
    /*border-radius: 0;*/
    /*-webkit-box-shadow: 0 0 10px #000;*/
    /*-moz-box-shadow: 0 0 10px #000;*/
    /*-o-box-shadow: 0 0 10px #000;*/
    /*-ms-box-shadow: 0 0 10px #000;*/
    /*box-shadow: 0 0 10px #000;*/
    /*text-align: left;*/
    /*color: #fff;*/
    /*}*/

    /*.modal a.close-modal {*/
    /*position: absolute;*/
    /*top: -12.5px;*/
    /*right: -12.5px;*/
    /*display: block;*/
    /*width: 30px;*/
    /*height: 30px;*/
    /*text-indent: -9999px;*/
    /*background: url("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAB4AAAAeCAYAAAA7MK6iAAAACXBIWXMAAAsTAAALEwEAmpwYAAAABGdBTUEAANjr9RwUqgAAACBjSFJNAABtmAAAc44AAPJxAACDbAAAg7sAANTIAAAx7AAAGbyeiMU/AAAG7ElEQVR42mJkwA8YoZjBwcGB6fPnz4w/fvxg/PnzJ2N6ejoLFxcX47Rp036B5Dk4OP7z8vL+P3DgwD+o3v9QjBUABBALHguZoJhZXV2dVUNDgxNIcwEtZnn27Nl/ZmZmQRYWFmag5c90dHQY5OXl/z98+PDn1atXv79+/foPUN9fIP4HxRgOAAggRhyWMoOwqKgoq6GhIZe3t7eYrq6uHBDb8/Pz27Gysloga/jz588FYGicPn/+/OapU6deOnXq1GdgqPwCOuA31AF/0S0HCCB0xAQNBU4FBQWB0NBQublz59oADV37Hw28ePHi74MHD/6ii3/8+HEFMGQUgQ6WEhQU5AeZBTWTCdkigABC9ylIAZeMjIxQTEyMysaNG/3+/v37AGTgr1+//s2cOfOXm5vbN6Caz8jY1NT0a29v76/v37//g6q9sHfv3khjY2M5YAgJgsyEmg0PYYAAQreUk4+PT8jd3V1l1apVgUAzfoIM2rlz5x9gHH5BtxAdA9PB1zNnzvyB+R6oLxoopgC1nBPZcoAAgiFQnLIDMb+enp5iV1eXBzDeHoI0z58//xcwIX0mZCkMg9S2trb+hFk+ffr0QCkpKVmQ2VA7QHYxAgQQzLesQMwjIiIilZWVZfPu3bstMJ+SYikyBmUzkBnA9HEMyNcCYgmQHVC7mAACCJagOEBBbGdnp7lgwYJEkIavX7/+BcY1SvAaGRl9tba2xohjMTGxL8nJyT+AWQsuxsbG9vnp06e/QWYdPHiwHmiWKlBcCGQXyNcAAQSzmBuoSQqYim3u37+/EKR48uTJv5ANB+bVr7Dga2xs/AkTV1JS+gq0AJyoQIkPWU9aWtoPkPibN2/2A/l6QCwJ9TULQADB4hcY//xKXl5eHt++fbsAUmxhYYHiM1DiAsr9R7ZcVVUVbikIdHd3/0TWIyws/AWYVsByAgICdkAxRSAWAGI2gACClV7C4uLiOv7+/lEgRZ8+ffqLLd6ABck3ZMuB6uCWrlu37je29HDx4kVwQisvL88FFqkaQDERUHADBBAomBl5eHiYgQmLE1hSgQQZgIUD1lJm69atf4HR8R1YKoH5QIPAWWP9+vV/gOI/gHkeQw+wGAXTwAJJ5t+/f/BUDRBA4NIEKMDMyMjICtQIiniG379/4yza7t69+//Lly8oDrty5co/bJaCAEwcZCkwwTJDLWYCCCCwxcDgY3z16hXDnTt3voP4EhISWA0BFgZMwNqHExh3jMiG1tbWsgHjnA2bHmAeBtdWwOL1MycnJ7wAAQggBmi+kgIW/OaKiorJwOLuFShO0LMSMPF9AUYBSpz6+vqixHlOTs4P9MIEWHaDsxSwYMoE2mEGFJcG5SKAAGJCqjv/AbPUn8ePH98ACQQHB6NUmZqamkzABIgSp5s3bwbHORCA1QDLAWZkPc7OzszA8oHl5cuXVy5duvQBGIXwWgoggGA+FgO6xkBNTS28r69vDrT2+Y1cIMDyJchX6KkXVEmAshd6KB06dAic94EO3AzkBwGxPhCLg8ptgACCZyeQp9jZ2b2AmsuAefM8tnxJCk5ISPgOLTKfAdNEOVDMA2QHLDsBBBC8AAFlbmCLwlZISCg5JSVlJizeQAaQaimoWAUFK0g/sGGwHiiWCMS2yAUIQAAxI7c4gEmeFZi4OJ48ecLMzc39CRiEmgEBASxA/QzA8vYvAxEgNjaWZc2aNezAsprp2LFjp4FpZRdQ+AkQvwLij0AMSoC/AQIIXklAC3AVUBoBxmE8sPXQAiyvN8J8fuPGjR/h4eHf0eMdhkENhOPHj8OT+NGjR88BxZuBOA5kJtRseCUBEECMSI0AdmgBDooDaaDl8sASTSkyMlKzpqZGU1paGlS7MABLrX83b978A6zwwakTmE0YgIkSnHpBfGCV+gxYh98qKSk5CeTeAxVeQPwUiN8AMSjxgdLNX4AAYkRqCLBAXcMHtVwSaLkMMMHJAvOq9IQJE9R8fHxElJWV1bEF8aNHj+7t27fvLTDlXwXGLyhoH0OD+DnU0k/QYAa1QP8BBBAjWsuSFWo5LzRYxKFYAljqiAHzqxCwIBEwMTERBdZeoOYMA7Bl+RFYEbwB5oS3IA9D4/IFEL+E4nfQ6IDFLTgvAwQQI5ZmLRtSsINSuyA0uwlBUyQPMPWD20/AKo8ByP4DTJTfgRgUjB+gFoEc8R6amGDB+wu5mQsQQIxYmrdMUJ+zQTM6NzQEeKGO4UJqOzFADQMZ/A1qCSzBfQXi71ALfyM17sEAIIAY8fQiWKAYFgIwzIbWTv4HjbdfUAf8RPLhH1icojfoAQKIEU8bG9kRyF0aRiz6YP0k5C4LsmUY9TtAADEyEA+IVfufGEUAAQYABejinPr4dLEAAAAASUVORK5CYII=") no-repeat 0 0;*/
    /*}*/

    /*.modal-spinner {*/
    /*display: none;*/
    /*width: 64px;*/
    /*height: 64px;*/
    /*position: fixed;*/
    /*top: 50%;*/
    /*left: 50%;*/
    /*margin-right: -32px;*/
    /*margin-top: -32px;*/
    /*background: url("data:image/gif;base64,R0lGODlhIAAgAPMAABEREf///0VFRYKCglRUVG5ubsvLy62trTQ0NCkpKU5OTuLi4vr6+gAAAAAAAAAAACH+GkNyZWF0ZWQgd2l0aCBhamF4bG9hZC5pbmZvACH5BAAKAAAAIf8LTkVUU0NBUEUyLjADAQAAACwAAAAAIAAgAAAE5xDISWlhperN52JLhSSdRgwVo1ICQZRUsiwHpTJT4iowNS8vyW2icCF6k8HMMBkCEDskxTBDAZwuAkkqIfxIQyhBQBFvAQSDITM5VDW6XNE4KagNh6Bgwe60smQUB3d4Rz1ZBApnFASDd0hihh12BkE9kjAJVlycXIg7CQIFA6SlnJ87paqbSKiKoqusnbMdmDC2tXQlkUhziYtyWTxIfy6BE8WJt5YJvpJivxNaGmLHT0VnOgSYf0dZXS7APdpB309RnHOG5gDqXGLDaC457D1zZ/V/nmOM82XiHRLYKhKP1oZmADdEAAAh+QQACgABACwAAAAAIAAgAAAE6hDISWlZpOrNp1lGNRSdRpDUolIGw5RUYhhHukqFu8DsrEyqnWThGvAmhVlteBvojpTDDBUEIFwMFBRAmBkSgOrBFZogCASwBDEY/CZSg7GSE0gSCjQBMVG023xWBhklAnoEdhQEfyNqMIcKjhRsjEdnezB+A4k8gTwJhFuiW4dokXiloUepBAp5qaKpp6+Ho7aWW54wl7obvEe0kRuoplCGepwSx2jJvqHEmGt6whJpGpfJCHmOoNHKaHx61WiSR92E4lbFoq+B6QDtuetcaBPnW6+O7wDHpIiK9SaVK5GgV543tzjgGcghAgAh+QQACgACACwAAAAAIAAgAAAE7hDISSkxpOrN5zFHNWRdhSiVoVLHspRUMoyUakyEe8PTPCATW9A14E0UvuAKMNAZKYUZCiBMuBakSQKG8G2FzUWox2AUtAQFcBKlVQoLgQReZhQlCIJesQXI5B0CBnUMOxMCenoCfTCEWBsJColTMANldx15BGs8B5wlCZ9Po6OJkwmRpnqkqnuSrayqfKmqpLajoiW5HJq7FL1Gr2mMMcKUMIiJgIemy7xZtJsTmsM4xHiKv5KMCXqfyUCJEonXPN2rAOIAmsfB3uPoAK++G+w48edZPK+M6hLJpQg484enXIdQFSS1u6UhksENEQAAIfkEAAoAAwAsAAAAACAAIAAABOcQyEmpGKLqzWcZRVUQnZYg1aBSh2GUVEIQ2aQOE+G+cD4ntpWkZQj1JIiZIogDFFyHI0UxQwFugMSOFIPJftfVAEoZLBbcLEFhlQiqGp1Vd140AUklUN3eCA51C1EWMzMCezCBBmkxVIVHBWd3HHl9JQOIJSdSnJ0TDKChCwUJjoWMPaGqDKannasMo6WnM562R5YluZRwur0wpgqZE7NKUm+FNRPIhjBJxKZteWuIBMN4zRMIVIhffcgojwCF117i4nlLnY5ztRLsnOk+aV+oJY7V7m76PdkS4trKcdg0Zc0tTcKkRAAAIfkEAAoABAAsAAAAACAAIAAABO4QyEkpKqjqzScpRaVkXZWQEximw1BSCUEIlDohrft6cpKCk5xid5MNJTaAIkekKGQkWyKHkvhKsR7ARmitkAYDYRIbUQRQjWBwJRzChi9CRlBcY1UN4g0/VNB0AlcvcAYHRyZPdEQFYV8ccwR5HWxEJ02YmRMLnJ1xCYp0Y5idpQuhopmmC2KgojKasUQDk5BNAwwMOh2RtRq5uQuPZKGIJQIGwAwGf6I0JXMpC8C7kXWDBINFMxS4DKMAWVWAGYsAdNqW5uaRxkSKJOZKaU3tPOBZ4DuK2LATgJhkPJMgTwKCdFjyPHEnKxFCDhEAACH5BAAKAAUALAAAAAAgACAAAATzEMhJaVKp6s2nIkolIJ2WkBShpkVRWqqQrhLSEu9MZJKK9y1ZrqYK9WiClmvoUaF8gIQSNeF1Er4MNFn4SRSDARWroAIETg1iVwuHjYB1kYc1mwruwXKC9gmsJXliGxc+XiUCby9ydh1sOSdMkpMTBpaXBzsfhoc5l58Gm5yToAaZhaOUqjkDgCWNHAULCwOLaTmzswadEqggQwgHuQsHIoZCHQMMQgQGubVEcxOPFAcMDAYUA85eWARmfSRQCdcMe0zeP1AAygwLlJtPNAAL19DARdPzBOWSm1brJBi45soRAWQAAkrQIykShQ9wVhHCwCQCACH5BAAKAAYALAAAAAAgACAAAATrEMhJaVKp6s2nIkqFZF2VIBWhUsJaTokqUCoBq+E71SRQeyqUToLA7VxF0JDyIQh/MVVPMt1ECZlfcjZJ9mIKoaTl1MRIl5o4CUKXOwmyrCInCKqcWtvadL2SYhyASyNDJ0uIiRMDjI0Fd30/iI2UA5GSS5UDj2l6NoqgOgN4gksEBgYFf0FDqKgHnyZ9OX8HrgYHdHpcHQULXAS2qKpENRg7eAMLC7kTBaixUYFkKAzWAAnLC7FLVxLWDBLKCwaKTULgEwbLA4hJtOkSBNqITT3xEgfLpBtzE/jiuL04RGEBgwWhShRgQExHBAAh+QQACgAHACwAAAAAIAAgAAAE7xDISWlSqerNpyJKhWRdlSAVoVLCWk6JKlAqAavhO9UkUHsqlE6CwO1cRdCQ8iEIfzFVTzLdRAmZX3I2SfZiCqGk5dTESJeaOAlClzsJsqwiJwiqnFrb2nS9kmIcgEsjQydLiIlHehhpejaIjzh9eomSjZR+ipslWIRLAgMDOR2DOqKogTB9pCUJBagDBXR6XB0EBkIIsaRsGGMMAxoDBgYHTKJiUYEGDAzHC9EACcUGkIgFzgwZ0QsSBcXHiQvOwgDdEwfFs0sDzt4S6BK4xYjkDOzn0unFeBzOBijIm1Dgmg5YFQwsCMjp1oJ8LyIAACH5BAAKAAgALAAAAAAgACAAAATwEMhJaVKp6s2nIkqFZF2VIBWhUsJaTokqUCoBq+E71SRQeyqUToLA7VxF0JDyIQh/MVVPMt1ECZlfcjZJ9mIKoaTl1MRIl5o4CUKXOwmyrCInCKqcWtvadL2SYhyASyNDJ0uIiUd6GGl6NoiPOH16iZKNlH6KmyWFOggHhEEvAwwMA0N9GBsEC6amhnVcEwavDAazGwIDaH1ipaYLBUTCGgQDA8NdHz0FpqgTBwsLqAbWAAnIA4FWKdMLGdYGEgraigbT0OITBcg5QwPT4xLrROZL6AuQAPUS7bxLpoWidY0JtxLHKhwwMJBTHgPKdEQAACH5BAAKAAkALAAAAAAgACAAAATrEMhJaVKp6s2nIkqFZF2VIBWhUsJaTokqUCoBq+E71SRQeyqUToLA7VxF0JDyIQh/MVVPMt1ECZlfcjZJ9mIKoaTl1MRIl5o4CUKXOwmyrCInCKqcWtvadL2SYhyASyNDJ0uIiUd6GAULDJCRiXo1CpGXDJOUjY+Yip9DhToJA4RBLwMLCwVDfRgbBAaqqoZ1XBMHswsHtxtFaH1iqaoGNgAIxRpbFAgfPQSqpbgGBqUD1wBXeCYp1AYZ19JJOYgH1KwA4UBvQwXUBxPqVD9L3sbp2BNk2xvvFPJd+MFCN6HAAIKgNggY0KtEBAAh+QQACgAKACwAAAAAIAAgAAAE6BDISWlSqerNpyJKhWRdlSAVoVLCWk6JKlAqAavhO9UkUHsqlE6CwO1cRdCQ8iEIfzFVTzLdRAmZX3I2SfYIDMaAFdTESJeaEDAIMxYFqrOUaNW4E4ObYcCXaiBVEgULe0NJaxxtYksjh2NLkZISgDgJhHthkpU4mW6blRiYmZOlh4JWkDqILwUGBnE6TYEbCgevr0N1gH4At7gHiRpFaLNrrq8HNgAJA70AWxQIH1+vsYMDAzZQPC9VCNkDWUhGkuE5PxJNwiUK4UfLzOlD4WvzAHaoG9nxPi5d+jYUqfAhhykOFwJWiAAAIfkEAAoACwAsAAAAACAAIAAABPAQyElpUqnqzaciSoVkXVUMFaFSwlpOCcMYlErAavhOMnNLNo8KsZsMZItJEIDIFSkLGQoQTNhIsFehRww2CQLKF0tYGKYSg+ygsZIuNqJksKgbfgIGepNo2cIUB3V1B3IvNiBYNQaDSTtfhhx0CwVPI0UJe0+bm4g5VgcGoqOcnjmjqDSdnhgEoamcsZuXO1aWQy8KAwOAuTYYGwi7w5h+Kr0SJ8MFihpNbx+4Erq7BYBuzsdiH1jCAzoSfl0rVirNbRXlBBlLX+BP0XJLAPGzTkAuAOqb0WT5AH7OcdCm5B8TgRwSRKIHQtaLCwg1RAAAOwAAAAAAAAAAAA==") #111 no-repeat center center;*/
    /*-webkit-border-radius: 8px;*/
    /*-moz-border-radius: 8px;*/
    /*-o-border-radius: 8px;*/
    /*-ms-border-radius: 8px;*/
    /*border-radius: 8px;*/
    /*}*/

    .c-m-header {
        text-align: center;
        background: #fff;
        padding: 5px;
    }

    .c-m-header img {
        max-width: 207px;
    }

    .cm-body-inner {

        padding: 10px;
        margin: auto;
        text-align: center;
    }

    .step-1 {
        max-width: 520px;
    }

    h3 {
        font-weight: 300;
        font-size: 20px;
        margin: 0;
        margin-bottom: 20px;
    }

    p {
        font-size: 16px;
    }

    table.product-tbl {
        margin-top: 20px;
        width: 60%;
        border-collapse: collapse;
        margin-left: 168px;
    }

    tr.prdct-head td {
        font-size: 12px;
        text-transform: uppercase;
        color: #fff;
        padding: 0 0 8px;
        border-bottom: 1px solid #fff;
    }

    tr.prdct-head {
        border-bottom: 1px solid #fff;
    }

    tr.prdct-item td {
        padding: 12px;
        font-size: 21px;
        font-weight: 600;
    }

    tr.prdct-item td p {
        font-size: 21px;
    }

    .qty-update {
        max-width: 110px;
        margin: 25px auto;
    }

    .qty-update p {
        font-size: 10px;
        text-transform: uppercase;
    }

    input.qty-box {
        width: 100%;
        height: 30px;
        border: 0;
        border-radius: 4px;
        margin: 5px 0;
        text-align: center;
        color: #47889f;
        font-size: 25px;
        font-weight: 500;
        font-family: 'Poppins', sans-serif;
        outline: none !important
    }

    .yellow-btn {
        line-height: 30px;
        background: #f8bb00;
        border: 0;
        color: #fff;
        font-size: 12px;
        display: inline-block;
        border-radius: 4px;
        font-weight: 500;
        font-family: 'Poppins', sans-serif;
        cursor: pointer;
        margin: 5px 0;
        transition: all .5s ease;
        border: 0;
        text-decoration: none;
        padding: 0px 15px;
    }

    .blue-btn {
        line-height: 30px;
        background: #343a40;
        border: 0;
        color: #fff;
        font-size: 12px;
        display: inline-block;
        border-radius: 4px;
        font-weight: 500;
        font-family: 'Poppins', sans-serif;
        cursor: pointer;
        margin: 5px 0;
        transition: all .5s ease;
        border: 0;
        text-decoration: none;
        padding: 0px 15px;
    }

    .yellow-btn:hover {
        background: #222;
    }

    .blue-btn:hover {
        background: #222;
    }

    .order-detail-wrap {
        display: -webkit-box;
        display: -ms-flexbox;
        display: flex;
        -ms-flex-wrap: wrap;
        flex-wrap: wrap;
        text-align: left;
        padding: 0px 30px;
    }

    .order-details {
        -webkit-box-flex: 0;
        -ms-flex: 0 0 66.666667%;
        flex: 0 0 66.666667%;
        max-width: 66.666667%;
        position: relative;
        width: 60%;
        min-height: 1px;
        padding-right: 30px;
        padding-left: 15px;
        margin-left: 168px;
    }

    .quick-checkout {
        -webkit-box-flex: 0;
        -ms-flex: 0 0 25%;
        flex: 0 0 25%;
        text-align: center !important;
        /*max-width: 25%;*/
        /*padding-left: 20px;*/
    }

    .submit-cart {
        -webkit-box-flex: 0;
        -ms-flex: 0 0 100%;
        flex: 0 0 100%;
        max-width: 100%;
        text-align: center;
    }

    .qty-update .yellow-btn {
        width: 100%;
    }

    .qty-update .blue-btn {
        width: 100%;
    }

    .order-detail-wrap h4 {
        font-weight: 300;
        font-size: 18px;
        margin: 0;
        margin-bottom: 20px;
        text-transform: uppercase;
    }

    .order-details tr.prdct-item td {
        font-size: 14px;
        font-weight: 400;
        padding: 12px 0;
        border-bottom: 1px solid #fff;
    }

    .order-details tr.prdct-item td p {
        font-size: 14px;
        font-weight: 600;
    }

    tr.total-price td {
        padding: 12px 0;
    }

    .copuon-lt label {
        font-size: 10px;
        text-transform: uppercase;
    }

    .copuon-lt {
        width: 160px;
    }

    .input-box {
        width: 100%;
        height: 30px;
        border: 0;
        border-radius: 4px;
        margin: 5px 0;
        padding: 0px 10px;
        text-align: left;
        color: #47889f;
        font-size: 12px;
        font-weight: 400;
        font-family: 'Poppins', sans-serif;
        outline: none !important;
    }

    .coupon-code {
        display: -webkit-box;
        display: -ms-flexbox;
        display: flex;
        -ms-flex-wrap: wrap;
        flex-wrap: wrap;
        text-align: left;
        align-items: flex-end;
        margin-left: 168px;
    }

    .coupn-btn {
        margin-left: 10px;
        text-transform: uppercase;
    }

    .coupn-btn .yellow-btn {
        text-transform: uppercase;
        padding: 0px 20px;
    }

    .coupn-btn .blue-btn {
        text-transform: uppercase;
        padding: 0px 20px;
    }

    .payment-method label {
        font-size: 10px;
        text-transform: uppercase;
    }

    .payment-method-options-active {
        background-color:#f8bb00;
        color:#fff;
        font-weight: 600;
    }

    .payment-method-options-inactive {
        background-color:#868e96;
        color:#fff;
        font-weight: 600;
    }

    .order-detail-wrap select {
        width: 100%;
        height: 30px;
        border: 0;
        border-radius: 4px;
        margin: 5px 0;
        padding: 0px 10px;
        text-align: left;
        color: #47889f;
        font-size: 11px;
        font-weight: 400;
        font-family: 'Poppins', sans-serif;
        outline: none !important;
    }

    .submit-cart .yellow-btn {
        font-size: 14px;
        padding: 5px 25px;
        height: auto;
        border: 1px solid #fff;
    }.submit-cart .blue-btn {
        font-size: 14px;
        padding: 5px 25px;
        height: auto;
        border: 1px solid #fff;
    }

    .prdct-name p span {
        display: inline-block;
        vertical-align: middle;
    }

    .payment-field.cvv-box {
        max-width: 100px;
        margin-left: auto;
    }

    .thankyou-section {
        max-width: 470px;
        margin: auto;
        padding: 40px 0;
    }

    .thankyou-section h3 {
        font-size: 26px;
        margin: 0;
        padding-bottom: 30px;
        margin-bottom: 30px;
        border-bottom: 1px solid #fff;
        position: relative;
    }

    .yellow-btn a:hover {
        color: #fff;
    }

    .blue-btn a:hover {
        color: #fff;
    }

    .thankyou-section h3:after {
        content: "";
        position: absolute;
        left: 50%;
        bottom: -35px;
        width: 42px;
        height: 62px;
        background: url('{{url('assets/images/white-icon.png')}}') no-repeat center center;
        background-size: contain;
        transform: translateX(-50%);
    }

    .return-back {
        margin-top: 50px;
    }

    .card-wrap {
        display: -webkit-box;
        display: -ms-flexbox;
        display: flex;
        -ms-flex-wrap: wrap;
        flex-wrap: wrap;
        justify-content: space-between;
        text-align: left;
    }

    .card-lt, .card-rt {
        -webkit-box-flex: 0;
        -ms-flex: 0 0 48%;
        flex: 0 0 48%;
        max-width: 48%;
        position: relative;
        width: 100%;
        min-height: 1px;
        padding-right: 15px;
        padding-left: 15px;
    }

    .card-wrap label {
        font-size: 10px;
        text-transform: uppercase;
    }

    .card-field-8 {
        -webkit-box-flex: 0;
        -ms-flex: 0 0 60%;
        flex: 0 0 60%;
        max-width: 60%;
        position: relative;
        width: 100%;
    }

    .card-field {
        display: -webkit-box;
        display: -ms-flexbox;
        display: flex;
        -ms-flex-wrap: wrap;
        flex-wrap: wrap;
        justify-content: space-between;
        text-align: left;
    }

    .card-field-full {
        -webkit-box-flex: 0;
        -ms-flex: 0 0 100%;
        flex: 0 0 100%;
        max-width: 100%;
    }

    .card-field-4 {
        -webkit-box-flex: 0;
        -ms-flex: 0 0 30%;
        flex: 0 0 30%;
        max-width: 30%;
        position: relative;
        width: 100%;
    }

    .card-field-5 {
        -webkit-box-flex: 0;
        -ms-flex: 0 0 45%;
        flex: 0 0 45%;
        max-width: 45%;
        position: relative;
        width: 100%;
    }

    span.req {
        color: red;
    }

    .card-wrap .submit-cart {
        margin-top: 40px;
    }

    @media (max-width: 767px) {
        tr.prdct-item td p {
            font-size: 16px;
        }

        .order-detail-wrap {
            padding: 0;
        }

        .order-details {
            -webkit-box-flex: 0;
            -ms-flex: 0 0 100%;
            flex: 0 0 100%;
            max-width: 60%;
            margin-left: 82px;
        }
        
        .quick-checkout {
            -webkit-box-flex: 0;
            -ms-flex: 0 0 100%;
            flex: 0 0 100%;
            max-width: 36%;
            margin-left: 245px;
        }

        .card-lt, .card-rt {
            -webkit-box-flex: 0;
            -ms-flex: 0 0 100%;
            flex: 0 0 100%;
            max-width: 100%;
        }

        .thankyou-section {
            padding: 30px 15px;
        }
    }
    
    @media only screen and (min-device-width: 768px) and (max-device-width: 1024px) {
        tr.prdct-item td p {
            font-size: 16px;
        }

        .order-detail-wrap {
            padding: 0;
        }

        .order-details {
            -webkit-box-flex: 0;
            -ms-flex: 0 0 100%;
            flex: 0 0 100%;
            max-width: 60%;
            margin-left: -22px;
        }
        
        .quick-checkout {
            -webkit-box-flex: 0;
            -ms-flex: 0 0 100%;
            flex: 0 0 100%;
            max-width: 36%;
            margin-left: 155px;
        }

        .card-lt, .card-rt {
            -webkit-box-flex: 0;
            -ms-flex: 0 0 100%;
            flex: 0 0 100%;
            max-width: 100%;
        }

        .thankyou-section {
            padding: 30px 15px;
        }
        
        .product-tbl{
            margin-left: -22px;
        }
   }
</style>