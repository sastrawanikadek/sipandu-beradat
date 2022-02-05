package com.sipanduberadat.guest.fragments

import android.content.Intent
import android.os.Bundle
import android.os.Handler
import android.os.Looper
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.view.animation.AnimationUtils
import androidx.fragment.app.Fragment
import androidx.lifecycle.ViewModelProvider
import androidx.viewpager.widget.ViewPager
import com.sipanduberadat.guest.R
import com.sipanduberadat.guest.activities.AddFamilyActivity
import com.sipanduberadat.guest.adapters.MainFamilyViewPagerAdapter
import com.sipanduberadat.guest.viewModels.MainViewModel
import kotlinx.android.synthetic.main.layout_main_family.view.*

class MainFamilyFragment: Fragment() {
    override fun onCreateView(
        inflater: LayoutInflater,
        container: ViewGroup?,
        savedInstanceState: Bundle?
    ): View? {
        val view = inflater.inflate(R.layout.layout_main_family, container, false)
        val viewModel = ViewModelProvider(activity!!).get(MainViewModel::class.java)

        view.view_pager.adapter = MainFamilyViewPagerAdapter(childFragmentManager)
        view.view_pager.addOnPageChangeListener(object: ViewPager.OnPageChangeListener {
            override fun onPageScrolled(position: Int, positionOffset: Float, positionOffsetPixels: Int) {}

            override fun onPageSelected(position: Int) {
                val animation = AnimationUtils.loadAnimation(view.context, if (position == 0)
                    R.anim.fade_in else R.anim.fade_out)
                view.btn_add.startAnimation(animation)
                Handler(Looper.getMainLooper()).postDelayed({
                    view.btn_add.visibility = if (position == 0) View.VISIBLE else View.GONE
                }, 300)
            }

            override fun onPageScrollStateChanged(state: Int) {}
        })
        view.tabs.setupWithViewPager(view.view_pager)

        view.swipe_refresh.setOnRefreshListener {
            Handler(Looper.getMainLooper()).postDelayed({
                view.swipe_refresh.isRefreshing = false
                viewModel.families.value = null
                viewModel.requestFamilies.value = null
            }, 300)
        }

        view.btn_add.setOnClickListener { _ ->
            val intent = Intent(view.context, AddFamilyActivity::class.java)
            startActivity(intent)
        }

        return view
    }
}