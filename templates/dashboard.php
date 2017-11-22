
<div class="tpui-license">
    <div class="tp-panel <?php if ($active): echo "active"; endif; ?>" id="tp-dashboard-active">
        <h3 class="tp-panel-title"><?php echo esc_html__("TP License Manager - TP Dashboard", "tp-dashboard"); ?></h3>
        <div class="tp-panel-content">
            <div class="tp-desc">
                <?php echo esc_html__("Insert your license information to enables updates.", "tp-dashboard"); ?>
            </div>
            <div class="tp-text ">
                <div class="form-field input-group icon-left">
                    <input type="text" placeholder="Input your email"
                           class="tp-input"
                           id="tpdb-email"
                           value="<?php
                           echo $email; ?>" <?php if ($active):echo "disabled"; endif; ?>>
                    <div class="input-group-addon ion-email"></div>
                </div>
                <br/>
                <div class="form-field input-group icon-left">
                    <input type="password" placeholder="Input your key" class="tp-input" id="tpdb-key" value="<?php
                    echo $key; ?>" <?php if ($active):echo "disabled"; endif; ?>>
                    <div class="input-group-addon ion-key"></div>
                </div>
                <br/>
                <span class="tp-submit-active">
                <button class="tp-btn-primary xs-mr-25" id="active-key"><?php echo esc_html__("Active", "tp-dashboard");
                    ?></button>
                    <button class="tp-btn-primary xs-mr-25"
                            id="change-key"><?php echo esc_html__("Change lisence", "tp-dashboard");
                        ?></button><span class="spinner"></span>
                    </span>
            </div>
            <br/><br/>
            <div class=" notify-error tp-error xs-mb-25"
                 style="display: none;"><?php echo esc_html__("Key is invalid, please check and re-activate", "tp-dashboard"); ?></div>
        </div>
    </div>
</div>