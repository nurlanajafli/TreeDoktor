<section class="panel panel-default p-n" id="salesRows">
    <div class="table-responsive" style="max-height: 40vh;">
        <table class="table table-striped b-t b-light" id="tbl_search_result">
            <thead>
            <tr>
                <th>Client Name</th>
                <th>Project</th>
                <th>Total For Services</th>
                <!--<th>Total Estimate</th>-->
                <th>Date Created</th>
                <th>Estimator</th>
            </tr>
            </thead>
            <tbody id="salesRowsTable">
                <?php $this->load->view('sales_report_rows_each'); ?>
            </tbody>
        </table>
    </div>
</section>
