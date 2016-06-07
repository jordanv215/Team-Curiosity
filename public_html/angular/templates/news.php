<div id="newsCarousel" class="carousel slide" data-ride="carousel">
	<!-- Indicators -->
	<ol class="carousel-indicators">
		<li data-target="#newsCarousel" data-slide-to="0" class="active"></li>
	</ol>

	<!-- Wrapper for slides -->
	<div class="carousel-inner" role="listbox">
		<div class="item active">
			<div class="carousel-header">
				<h3>{{newsArticle.title}}</h3>
				<p>{{newsArticle.description}}</p>
			</div>
		</div>

		<!-- Left and right controls -->
		<a class="left carousel-control" href="#newsCarousel" role="button" data-slide="prev">
			<span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span>
			<span class="sr-only">Previous</span>
		</a>
		<a class="right carousel-control" href="#newsCarousel" role="button" data-slide="next">
			<span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span>
			<span class="sr-only">Next</span>
		</a>
	</div>
</div>

<h3>Random News</h3>
<div class="container">
	<div class="row">
		<div class="col-xs-3 news-random">
			<img class="img-responsive" src="../public_html/image/mars.png" alt="mars">
		</div>
		<div class="col-xs-9">
			<p>News-1</p>
		</div>
	</div>
	<div class="row">
		<div class="col-xs-3 news-random">
			<img class="img-responsive" src="../public_html/image/mars.png" alt="mars">
		</div>
		<div class="col-xs-9">
			<p>News-2</p>
		</div>
	</div>
	<div class="row">
		<div class="col-xs-3 news-random">
			<img class="img-responsive" src="../public_html/image/mars.png" alt="mars">
		</div>
		<div class="col-xs-9">
			<p>News-3</p>
		</div>
	</div>
</div>