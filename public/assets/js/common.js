//http로 진입시 https 로 변경
if(location.href.substring(0,7) == 'http://'){
	location.href = location.href.replace('http://','https://');
}

// window.popup 함수
function js_popup(url, pop_name, w, h, z) {

    // 사이즈 처리
    if ( !w ) { w = 500; }
    if ( !h ) {	h = 400; }
    if ( !z ) {	z = 1; }

    let window_left	= (screen.width / 2) - (w / 2);
    if ( window_left < 0 ) {
        window_left = 0;
    }
    let window_top	= (screen.height / 2) - (h / 2);
    if ( window_top < 0 ) {
        window_top = 0;
    }

    let js_pop_form = window.open(url, pop_name,"top="+window_top+",left="+window_left+",toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars="+z+",resizable="+z+",width="+w+",height="+h);
    js_pop_form.resizeTo(w, h);
    js_pop_form.focus();
}

// csrf ajax 사용시 필요
$.ajaxPrefilter(function (options, originalOptions) {
	if (!options.processData && !options.contentType) {
		options.data.append('csrf_token', getCsrfCookie('csrf_cookie'));
	} else if (options.type.toLowerCase() === 'post') {
		options.data = $.param($.extend({}, originalOptions.data, {
			csrf_token: getCsrfCookie('csrf_cookie')
		}))
	}
});

function getCsrfCookie(cname) {
	let name = cname + '=';
	let decodedCookie = decodeURIComponent(document.cookie);
	let ca = decodedCookie.split(';');

	for(let i = 0; i <ca.length; i++) {
		let c = ca[i];
		while (c.charAt(0) == ' ') {
			c = c.substring(1);
		}

		if (c.indexOf(name) == 0) {
			return c.substring(name.length, c.length);
		}
	}

	return '';
}

// 페이지 로딩 후 처리
$(document).ready(function(){
	// Add active state to sidbar nav links
	let path = window.location.href; // because the 'href' property of the DOM element is the absolute path
	$("#layoutSidenav_nav .sb-sidenav a.nav-link").each(function() {
		if (this.href === path) {
			$(this).addClass("active");
		}
	});

	// Toggle the side navigation
	$("#sidebarToggle").on("click", function(e) {
		e.preventDefault();
		$("body").toggleClass("sb-sidenav-toggled");
	});

	// dropdown
	let dropdownElementList = [].slice.call(document.querySelectorAll('.dropdown-toggle'))
	dropdownElementList.map(function (dropdownToggleEl) {
		return new bootstrap.Dropdown(dropdownToggleEl)
	});

	//메뉴열때 다른메뉴 닫기
	$(".collapse").on("show.bs.collapse", function(e) {
		//$(".collapse").removeClass('show');
	});

	try{
		//마지막 선택 메뉴 활성화
		let myCollapse = $("input[name=sel_admin_leftmenu]").val();
		$("#"+myCollapse).collapse('show');
	}catch(e){

	}

	// 입력시 히스토리 이슈로 인해 autocomplete:off 추가
	$(".sel_date").attr("autocomplete","off");
	$(".sel_date").datepicker({
		changeMonth: true,
		changeYear: true,
		showMonthAfterYear: true,
		yearRange: 'c-5:c+5',
		dayNamesMin: ['일', '월', '화', '수', '목', '금', '토'], // 요일의 한글 형식.
		monthNamesShort: ['1월','2월','3월','4월','5월','6월','7월','8월','9월','10월','11월','12월'],
		dateFormat: "yy-mm-dd",
		altFormat: "yy-mm-dd",
		showButtonPanel: true,
		nextText: '다음 달',
		prevText: '이전 달',
		currentText: '오늘',
		closeText: '닫기'
	});
});
