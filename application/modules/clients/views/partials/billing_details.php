<div class="table-responsive">
    <table class="table text-center">
        <thead>
        <th>Type</th>
        <th class="text-center">Number</th>
        <th class="text-center">Expiration</th>
        <th class="text-center">Cardholder</th>
        <th class="text-center">Action</th>
        </thead>
        <tbody class="cards-list">
        <?php if(isset($cards) && !empty($cards)) : ?>
            <?php foreach($cards as $card) : ?>
                <?php $this->load->view('partials/cc_row', $card);?>
            <?php endforeach;?>
        <?php else : ?>
            <tr class="no_record_find" colspan="5"><td>No records found</td></tr>
        <?php endif;?>
        </tbody>
    </table>
</div>