{if !$content_only}
		</div>
		<div class="clearblock"></div>
	</div>
<!-- Footer -->
	<div id="footer">
		{$HOOK_FOOTER}
		{if $PS_ALLOW_MOBILE_DEVICE}
					<p class="mobile_link clear"><a href="{$link->getPageLink('index', true)}?mobile_theme_ok">{l s='Browse the mobile site'}</a></p>
		{/if}
	</div>
</div>
</div>
</div>
	{/if}
</body>
</html>
