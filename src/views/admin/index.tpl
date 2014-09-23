{extends file="$adminViewsDir/parent.tpl"}
{block name=header}
<style type="text/css">
.stat-toolbar {
	margin: 18px 0 0;
}

.stats {
	margin: 19px 0;
	text-align: center;
}

.stats .stat strong {
	font-size: 1.5em;
}

.stat {
	background: #fff;
	box-shadow: 1px 1px 0px #fff;
	border: 1px solid #ccc;
	padding: 10px;
	border-radius: 2px;
	margin: 0 0 30px;
}

.stat .title {
	font-size: 18px;
	font-weight: bold;
	padding: 0 15px 5px;
	border-bottom: 1px solid #ccc;
	color: #444;
}

.stat .value {
	font-size: 28px;
	font-weight: bold;
	padding: 0 15px;
	color: #444;
}

.stat .value .change {
	font-size: 14px;
}

.stat .shortcuts {
	padding: 10px 0;
}

.stat .shortcuts .shortcut {
	padding: 3px 7px;
	border-radius: 16px;
	color: #999;
	border: 1px solid transparent;
	font-size: 12px;
	margin-right: 5px;
	outline: none;
	text-decoration: none;
	-webkit-touch-callout: none;
	-webkit-user-select: none;
	-khtml-user-select: none;
	-moz-user-select: none;
	-ms-user-select: none;
	user-select: none;
}

.stat .shortcuts .shortcut:hover {
	color: #333;
	background: #eee;
	text-decoration: none;
	border: 1px solid #eee;
}

.stat .shortcuts .shortcut.active {
	color: #333;
	background: #eee;
}

h3 {
	text-transform: uppercase;
	color: #777;
	font-size: 14px;
	padding: 5px 30px;
	font-weight: normal;
	border-bottom: 1px solid #ccc;
	text-shadow: 2px 2px 2px #fff;
}
.chart {
	position: relative;
	margin: 15px 0 0;
}
.chart p {
	color: #999;
	text-align: center;
}
/* Chart data */
.chart path {
	stroke: #09f;
	stroke-width: 2;
	fill: none;
}
.chart circle {
	fill: #08c;
	stroke-width: 2;
	stroke: #fff;
}
.chart circle.hover { cursor: pointer; }
/* Axes */
.axis { shape-rendering: crispEdges; }
.x.axis line, .y.axis line { stroke: #eee; stroke-width: 1; }
.x.axis .minor, .y.axis .minor { stroke-opacity: .5; }
.x.axis path, .y.axis path { display: none; }
.x.axis text, .y.axis text { fill: #999; font-size: 11px; }
/* Info popover box */
.infobox {
	border: 2px solid steelblue;
	border-radius: 4px;
	box-shadow: #333333 0px 0px 10px;
	margin: 200px auto;
	padding: 5px 10px;
	background: rgba(255, 255, 255, 0.8);
	position: absolute;
	top: 0px;
	left: 0px;
	z-index: 10500;
	font-weight: bold;
}
</style>
<script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/d3/3.4.11/d3.min.js"></script>
<script type="text/javascript">
{literal}
	function getDate(d) {
		if (typeof d != 'undefined') {
			var dt = moment(d.day, "YYYY-MM-DD").toDate();
			dt.setHours(0);
			dt.setMinutes(0);
			dt.setSeconds(0);
			dt.setMilliseconds(0);
			return dt;			
		}
	}

	function showData(obj, name, d) {
		var coord = d3.mouse(obj);
		var infobox = d3.select('.infobox.' + name);
		// now we just position the infobox roughly where our mouse is
		infobox.style("left", (coord[0]) + "px" );
		infobox.style("top", (coord[1] - 175) + "px");
		$('.infobox.' + name).html(d);
		$('.infobox.' + name).show();
	}

	function hideData(name) {
		$('.infobox.' + name).hide();
	}

	var chartWidth;

	var drawChart = function(name) {
		var chartName = '.chart.' + name;
		var data = chartData[name];

		$(chartName).html('');

		// check for empty data
		if (data.length == 0) {
			$(chartName).append("<p><em>No data points have been captured yet.</em></p>")
			return;
		}

		// get container size of chart
		chartWidth = $(chartName).innerWidth();

		// define dimensions of graph
		var m = [15, 15, 60, 70]; // margins
		var w = chartWidth - m[1] - m[3]; // width
		var h = 200 - m[0] - m[2]; // height

		data.sort(function(a, b) {
			var d1 = getDate(a);
			var d2 = getDate(b);
			if (d1 == d2) return 0;
			if (d1 > d2) return 1;
			return -1;
		});

		var domain = chartIntervals[name];
		var granularity = chartGranularities[name];
		if (granularity == 'day') granularity = d3.time.days;
		else if (granularity == 'week') granularity = d3.time.weeks;
		else if (granularity == 'month') granularity = d3.time.months;
		else if (granularity == 'year') granularity = d3.time.years;

		// X scale will fit all values from data[] within pixels 0-w
		//var x = d3.scale.linear().domain([0, data.length]).range([0, w]);
		var x = d3.time.scale().domain(domain).range([0, w]);

		// Y scale will fit values from 0-10 within pixels h-0 (Note the inverted domain for the y-scale: bigger is up!)
		var maxY = d3.max(data, function(d) { return parseFloat(d.val); });
		var y = d3.scale.linear().domain([0,maxY]).range([h, 0]);

		// create a line function that can convert data[] into x and y points
		var line = d3.svg.line()
		// assign the X function to plot our line as we wish
		.x(function(d, i) {
			// return the X coordinate where we want to plot this datapoint
			return x(getDate(d)); //x(i);
		})
		.y(function(d) {
			// return the Y coordinate where we want to plot this datapoint
			return y(parseFloat(d.val));
		});

		function xx(e) { return x(getDate(e)); };
		function yy(e) { return y(parseFloat(e.val)); };

		// Add an SVG element with the desired dimensions and margin.
		var graph = d3.select(chartName).append("svg:svg")
		.attr("width", w + m[1] + m[3])
		.attr("height", h + m[0] + m[2])
		.append("svg:g")
		.attr("transform", "translate(" + m[3] + "," + m[0] + ")");

		// create xAxis
		var xAxis = d3.svg.axis().scale(x).ticks(granularity, 1).tickSize(0).tickSubdivide(true);
		// Add the x-axis.
		graph.append("svg:g")
		.attr("class", "x axis")
		.attr("transform", "translate(0," + h + ")")
		.call(xAxis)
		.selectAll("text")  
		     .style("text-anchor", "end")
		     .attr("dx", "-.8em")
		     .attr("dy", "1em")
		     .attr("transform", function(d) {
		         return "rotate(-45)" 
		     });

		// create left yAxis
		var yAxisLeft = d3.svg.axis().scale(y).ticks(3).tickSize(-w-m[1]-m[3]).tickSubdivide(true).orient("left"); //.tickFormat(formalLabel);
		// Add the y-axis to the left
		graph.append("svg:g")
		.attr("class", "y axis")
		.attr("transform", "translate(-25,0)")
		.call(yAxisLeft);

		// Add the line by appending an svg:path element with the data line we created above
		// do this AFTER the axes above so that the line is above the tick-lines
		graph.append("svg:path").attr("d", line(data));

		graph
		.selectAll("circle")
		.data(data)
		.enter().append("circle")
		.attr("r", 5)
		.attr("cx", xx)
		.attr("cy", yy)
		.on("mouseover", function(d) {
			showData(this, name, d.val);
			d3.select(this).attr('class', 'hover').attr('r','7');
		})
		.on("mouseout", function(){
			hideData(name);
			d3.select(this).attr('class', '').attr('r','5');
		});

		$(chartName).append("<div class='infobox " + name + "' style='display:none;'>Test</div>");
	}

	var draw = function(start, end) {
		for (var i in charts) {
			var name = charts[i];

			drawChart(name);
		}
	}

	var setChartInterval = function(metric, interval) {
		var start = moment(), end = moment();

		switch(interval)
		{
		case 'last-7-days':
			start = moment().subtract('days', 7);
		break;
		case 'last-7-weeks':
			start = moment().subtract('weeks', 7);
		break;
		case 'last-7-months':
			start = moment().subtract('months', 7);
		break;
		case 'last-7-years':
			start = moment().subtract('years', 7);
		break;
		case 'this-week':
			start = moment().startOf('week');
		break;
		case 'last-week':
			start = moment().subtract('week', 1).startOf('week');
			end = moment().subtract('week', 1).endOf('week');
		break;
		case 'this-month':
			start = moment().startOf('month');
		break;
		case 'last-month':
			start = moment().subtract('month', 1).startOf('month');
			end = moment().subtract('month', 1).endOf('month');
		break;
		case 'this-year':
			start = moment().startOf('year');
		break;
		case 'last-year':
			start = moment().subtract('year', 1).startOf('year');
			end = moment().subtract('year', 1).endOf('year');
		break;
		}

		chartIntervals[metric] = [start.toDate(),end.toDate()];

		if (checkLoadData(metric, interval))
			drawChart(metric);
	}

	var checkLoadData = function(metric, interval) {
		// check if the data needs to be loaded
		if (chartLoadedIntervals[metric] != interval) {
			//console.log( 'Need to load data for interval ' + interval + ' in ' + metric );

			var interval = chartIntervals[metric];

			$.get('/statistics/' + metric, {
					start: moment(interval[0]).unix(),
					// add some time to interval so that the present value is included (if we selected an end date within 1/2 min. of present time)
					end: moment(interval[1]).unix() + 1800
				}, function(result) {
					if (result.data) {
						chartData[metric] = result.data;
						chartLoadedIntervals[metric] = interval;
					}

					drawChart(metric);
				}, 'json');

			return false;
		} else {
			//console.log( 'Already have data for interval ' + interval + ' in ' + metric );

			return true;
		}
	}
{/literal}

	var chartData            = {$chartData|json_encode};
	var charts               = {$chartNames|json_encode};
	var chartIntervals       = {};
	var chartGranularities   = {$chartGranularities|json_encode};
	var chartLoadedIntervals = {$chartLoadedIntervals|json_encode}

	$(function() {
		$(window).resize(function() {
			draw();
		});

		$('.shortcut').click(function() {
			var metric = $(this).data('metric');
			var interval = $(this).data('interval');

			if (interval == 'custom') {
				// TODO
			} else {
				setChartInterval(metric, interval);
			}

			$('.shortcut[data-metric=' + metric + ']').removeClass('active');
			$('.shortcut[data-metric=' + metric + '][data-interval=' + interval + ']').addClass('active');

			return false;
		});

		$('.shortcut.active').click();
	});
</script>
{/block}
{block name=main}

{foreach from=$metrics key=section item=sub}
	<div class="row">
		<h3>{$section}</h3>
		{foreach from=$sub key=chart item=c}
			<div class="col-md-{if isset($c.span)}{$c.span}{else}4{/if}">
				<div class="stat">
					<div class="title">
						{$c.name}
					</div>
					{if $c.hasChart}
						<div class="shortcuts">
							{if $c.granularity == $smarty.const.STATISTIC_GRANULARITY_DAY}
								<a class="shortcut active" data-metric="{$chart}" data-interval="last-7-days" href="#">
									last 7
								</a>
								<a class="shortcut" data-metric="{$chart}" data-interval="this-week" href="#">
									this week
								</a>
								<a class="shortcut" data-metric="{$chart}" data-interval="last-week" href="#">
									last week
								</a>
								<a class="shortcut" data-metric="{$chart}" data-interval="this-month" href="#">
									this month
								</a>
								<a class="shortcut" data-metric="{$chart}" data-interval="last-month" href="#">
									last month
								</a>
							{else if $c.granularity == $smarty.const.STATISTIC_GRANULARITY_WEEK}
								<a class="shortcut active" data-metric="{$chart}" data-interval="last-7-weeks" href="#">
									last 7
								</a>
								<a class="shortcut" data-metric="{$chart}" data-interval="this-month" href="#">
									this month
								</a>
								<a class="shortcut" data-metric="{$chart}" data-interval="last-month" href="#">
									last month
								</a>
								<a class="shortcut" data-metric="{$chart}" data-interval="this-year" href="#">
									this year
								</a>
								<a class="shortcut" data-metric="{$chart}" data-interval="last-year" href="#">
									last year
								</a>
							{else if $c.granularity == $smarty.const.STATISTIC_GRANULARITY_MONTH}
								<a class="shortcut active" data-metric="{$chart}" data-interval="last-7-months" href="#">
									last 7
								</a>
								<a class="shortcut" data-metric="{$chart}" data-interval="this-year" href="#">
									this year
								</a>
								<a class="shortcut" data-metric="{$chart}" data-interval="last-year" href="#">
									last year
								</a>
							{else if $c.granularity == $smarty.const.STATISTIC_GRANULARITY_YEAR}
								<a class="shortcut active" data-metric="{$chart}" data-interval="last-7-years" href="#">
									last 7
								</a>
							{/if}
							<a class="shortcut" data-metric="{$chart}" data-interval="custom" href="#">
								custom
							</a>
						</div>
					{/if}
					<div class="value">
						{$c.prefix}{$c.abbreviated_value}{$c.suffix}
						{if $c.delta !== false}
							{if $c.delta == 0}
								
							{else}
								<span class="{if $c.delta > 0}text-success{else if $c.delta < 0}text-danger{/if} change">
									<span class="glyphicon {if $c.delta > 0}glyphicon-arrow-up{else if $c.delta < 0}glyphicon-arrow-down{/if}"></span>
									{$c.prefix}{$c.abbreviated_delta}{$c.suffix}
								</span>
							{/if}
						{/if}
					</div>
					{if $c.hasChart}
						<div class="chart {$chart}"></div>
					{/if}
				</div>
			</div>
		{/foreach}
	</div>
{/foreach}

{/block}