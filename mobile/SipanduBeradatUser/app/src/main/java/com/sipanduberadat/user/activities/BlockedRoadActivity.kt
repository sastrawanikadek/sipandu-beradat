package com.sipanduberadat.user.activities

import androidx.appcompat.app.AppCompatActivity
import android.os.Bundle
import androidx.lifecycle.ViewModelProvider
import com.sipanduberadat.user.R
import com.sipanduberadat.user.adapters.BlockedRoadViewPagerAdapter
import com.sipanduberadat.user.viewModels.BlockedRoadViewModel
import kotlinx.android.synthetic.main.activity_blocked_road.*

class BlockedRoadActivity : AppCompatActivity() {
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_blocked_road)
        val viewModel = ViewModelProvider(this).get(BlockedRoadViewModel::class.java)

        view_pager.adapter = BlockedRoadViewPagerAdapter(supportFragmentManager)
        viewModel.currentPage.observe(this, { view_pager.currentItem = it })
    }
}