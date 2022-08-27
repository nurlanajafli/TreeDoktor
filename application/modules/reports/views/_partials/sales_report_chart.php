<section class="panel panel-default" id="salesChart">
    <header class="panel-heading">Sales Report Chart
        <button class="btn btn-info pull-right exportPdf m-sides-10" style="margin-top:-8px;">Export to PDF</button>
        <button class="btn btn-info pull-right showHideServices m-sides-10" style="margin-top:-8px;">Show/Hide Services</button>
		<a href="#" tagrget="_blank" class="btn btn-info pull-right enDisableServices m-sides-10" style="margin-top:-8px;">Select/Unselect Services</a>
        <form name="pdfForm" target="_blank" id="pdfForm" method="post" action="<?php echo base_url('/reports/get_sales_pdf');?>"></form>
    </header>
    <div class="table-responsive p-15" style="/*max-height: 60vh;*/">
        <canvas id="flot-bar" style="/*height: 55vh; */max-width: 100%;"></canvas>
    </div>
</section>
