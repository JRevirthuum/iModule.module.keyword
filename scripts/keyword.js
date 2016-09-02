/**
 * @todo 모두 다시 개발되어야 합니다.
 *
var Keyword = {
	target:null,
	timeout:null,
	startLiveSearch:function(object) {
		this.target = object;
		this.liveSearch();
	},
	stopLiveSearch:function(object) {
		if (this.timeout == null) return;
		clearTimeout(this.timeout);
		this.timeout = null;
		
		if (this.target !== null) {
			setTimeout(Keyword.resetLiveSearch,100);
		}
	},
	liveSearch:function() {
		if (Keyword.target == null) return;
		
		var object = Keyword.target;
		if (object.val().length > 0 && object.data("lastKeyword") != object.val()) {
			object.data("lastKeyword",object.val());
			$.ajax({
				type:"POST",
				url:ENV.getProcessUrl("keyword","livesearch"),
				data:{keyword:object.val()},
				dataType:"json",
				success:function(result) {
					if (result.success == true) {
						if (result.keywords.length == 0) {
							object.parents(".liveSearchControl").find("ul").remove();
							object.parents(".liveSearchControl").removeClass("liveSearchControlExtend");
						} else {
							if (object.parents(".liveSearchControl").find("ul").length == 0) {
								object.parents(".liveSearchControl").append($("<ul>"));
								object.parents(".liveSearchControl").addClass("liveSearchControlExtend");
							}
							var list = object.parents(".liveSearchControl").find("ul");
							list.empty();
							
							for (var i=0, loop=result.keywords.length;i<loop;i++) {
								var item = $("<li>").html(result.keywords[i].keyword);
								item.data("keyword",result.keywords[i].keyword);
								item.on("click",function() {
									$(this).parents(".liveSearchControl").find("input").data("lastKeyword",$(this).data("keyword"));
									$(this).parents(".liveSearchControl").find("input").val($(this).data("keyword"));
									$(this).parents("form").submit();
								});
								list.append(item);
							}
						}
						Keyword.timeout = setTimeout(Keyword.liveSearch,500);
					} else {
						object.parents(".liveSearchControl").find("ul").remove();
					}
				},
				error:function() {
					Keyword.timeout = setTimeout(Keyword.liveSearch,1000);
				}
			});
		} else {
			Keyword.timeout = setTimeout(Keyword.liveSearch,200);
		}
	},
	resetLiveSearch:function() {
		Keyword.target.data("lastKeyword",null);
		Keyword.target.parents(".liveSearchControl").removeClass("liveSearchControlExtend");
		Keyword.target.parents(".liveSearchControl").find("ul").remove();
		Keyword.target = null;
	}
};

$(document).ready(function() {
	$(".liveSearchControl > input").attr("autocomplete","off");
	
	$(".liveSearchControl > input").on("focus",function(event) {
		Keyword.startLiveSearch($(this));
	});
	
	$(".liveSearchControl > input").on("blur",function(event) {
		Keyword.stopLiveSearch($(this));
	});
	
	$(".liveSearchControl > input").on("keydown",function(event) {
		var items = $(this).parents(".liveSearchControl").find("li");
		
		if (event.keyCode == 38 || event.keyCode == 40 || event.keyCode == 27) {
			if (items.length == 0) return;
			var index = items.index(items.filter(".selected"));

			if (event.keyCode == 38 && index > 0) index--;
			if (event.keyCode == 40 && index < items.length - 1) index++;
			if (!~index) index = 0;
			
			$(items).removeClass("selected");
			$(items).eq(index).addClass("selected");
			event.preventDefault();
			
			var blockHeight = $(items).eq(index).parent().height();
			var blockScroll = $(items).eq(index).parent().scrollTop();
			var itemHeight = $(items).eq(index).outerHeight(true);
			
			if (blockHeight + blockScroll < (index + 1) * itemHeight) {
				$(items).eq(index).parent().scrollTop((index + 1) * itemHeight - blockHeight);
			}
			
			if (blockScroll > index * itemHeight) {
				$(items).eq(index).parent().scrollTop(index * itemHeight);
			}
			
			$(this).data("lastKeyword",$(items).eq(index).data("keyword"));
			$(this).val($(items).eq(index).data("keyword"));
		}
		
		if (event.keyCode == 13) {
			$(this).parents("form").submit();
		}
	});
});
*/