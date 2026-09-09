<h1>BluePrint3D - Change Order Email for Magento 2 ✉️</h1>

<p>
    <a href="#"><img src="https://img.shields.io/badge/Magento-2.4.x-orange.svg" alt="Magento Version" /></a>
    <a href="#"><img src="https://img.shields.io/badge/PHP-8.1%20|%208.2%20|%208.3-blue.svg" alt="PHP Version" /></a>
    <a href="#"><img src="https://img.shields.io/badge/License-Proprietary-lightgrey.svg" alt="License" /></a>
</p>

<p>A Magento 2 admin module that lets store staff correct the customer email address on an existing order directly from the order view page — no database access required.</p>

<hr />

<h2>🛑 The Problem</h2>
<p>Customers regularly place orders with a mistyped email address, and out of the box Magento gives admins no safe way to fix it. The email on an order is tied to the customer record it was placed under, so a naive edit can silently detach the order from its account or collide with an email already used by someone else.</p>

<h2>🛠️ The Solution</h2>
<p>This module adds an <strong>"Edit Order Email"</strong> button to the admin order view page. Submitting a new address updates the order (and the customer account, if the order belongs to one) safely:</p>
<ul>
    <li>If the new email is unused, or already belongs to the same customer, the order and customer record are updated immediately.</li>
    <li>If the new email already belongs to a <em>different</em> customer, the admin is asked to confirm before the order is moved to that customer's account.</li>
    <li>Guest orders are updated in place with no customer account changes.</li>
</ul>
<p>Every change is recorded as a comment in the order's status history, noting the old/new email, the customer_id involved, and which admin user made the change.</p>

<h2>✨ Features</h2>
<ul>
    <li><strong>In-Context Editing:</strong> Edit the order email without leaving the order view page.</li>
    <li><strong>Guest & Customer Safe:</strong> Correctly handles guest orders, matching accounts, and conflicting accounts differently.</li>
    <li><strong>Confirmation Step:</strong> Never silently reassigns an order to another customer — always requires explicit confirmation.</li>
    <li><strong>Full Audit Trail:</strong> Logs every email change to the order's status history, including which admin made it.</li>
    <li><strong>Sales Grid Aware:</strong> Keeps the sales_order_grid email column in sync with the change.</li>
</ul>

<hr />

<h2>📦 Installation</h2>

<p><strong>1. Install via Composer</strong></p>
<pre><code>composer require blueprint3d/module-change-order-email</code></pre>

<p><strong>2. Enable the module</strong></p>
<pre><code>php bin/magento module:enable BluePrint3D_ChangeOrderEmail</code></pre>

<p><strong>3. Run setup upgrade</strong></p>
<pre><code>php bin/magento setup:upgrade</code></pre>

<p><strong>4. Compile and flush cache</strong></p>
<pre><code>php bin/magento setup:di:compile
php bin/magento cache:flush</code></pre>

<hr />

<h2>👨‍💻 Usage</h2>
<p>Open any order in <strong>Sales &gt; Orders</strong>, click <strong>Edit Order Email</strong> in the page header, enter and confirm the new email address, and submit. If the new address already belongs to another customer, you'll be prompted to confirm moving the order to that customer before the change is applied.</p>

<hr />

<h2>📜 License</h2>
<p><strong>Copyright &copy; 2026 BluePrint3D Ltd. All rights reserved.</strong></p>

<p>This software is provided free of charge for personal or commercial use. However, the resale, redistribution, or sublicensing of this source code, modified or unmodified, for direct financial gain is strictly prohibited.</p>

<p>Please see the <code>LICENSE.txt</code> file for full terms and conditions.</p>

<p>
    <strong>Owned by:</strong> BluePrint3D Ltd (Company Registration Number: 13473806)<br />
    <strong>Email:</strong> <a href="mailto:support@blueprint3d.dev">support@blueprint3d.dev</a>
</p>
