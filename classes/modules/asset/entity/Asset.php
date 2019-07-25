<?php

/**
 * Description of Asset
 *
 * @author oleg
 */

class ModuleAsset_EntityAsset extends Entity{
    
    public function Init() {
        $this->setAsset( $this->ModuleAsset_CreateAssetType($this->getFile()) );
    }
}
