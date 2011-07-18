
<div class="cart_mini">

<div class="productWrapper">
<?php foreach($this->products as $product): ?>
	<div class="product">
		<div class="removeButton"><a href="<?php echo $product['remove_link']; ?>" title="<?php echo $product['remove_link_title']; ?>"><?php echo $product['remove_link_text']; ?></a></div>
        <div class="productName"><?php echo $product['name']; ?></div>
		<div class="info">
        	<?php if($this->showOptions && $product['product_options']): ?>
			<div class="optionswrapper">
				<ul class="productOptions">
				<?php foreach($product['product_options'] as $option): ?>
					<li><strong><?php echo $option['label']; ?>:</strong> <?php echo $option['value']; ?></li>
				<!--<div class="option"><span class="optionname">OPTION:</span> PRODUCT OPTION</div>-->
				<?php endforeach; ?>
				</ul>
			</div>
			<?php endif; ?>
			<div class="price"><?php echo $product['quantity']; ?> x <?php echo $product['price']; ?> = <span class="total"><?php echo $product['total_price']; ?></span></div>
		</div>
	<div class="clear">&nbsp;</div>
	</div>
<?php endforeach; ?>
    <div class="subtotal"><span class="label"><?php echo $this->subTotalLabel; ?></span> <?php echo $this->subTotalPrice; ?></div><?php if ($this->cartJumpTo): ?>
    <div class="cart"><a class="button" href="<?php echo $this->cartJumpTo; ?>"><span><?php echo $this->cartLabel; ?></span></a></div><?php endif; if ($this->checkoutJumpTo): ?>
	<div class="checkout"><a class="button dark" href="<?php echo $this->checkoutJumpTo; ?>"><?php echo $this->checkoutJumpToLabel; ?></a></div><?php endif; ?>
</div>
</div>