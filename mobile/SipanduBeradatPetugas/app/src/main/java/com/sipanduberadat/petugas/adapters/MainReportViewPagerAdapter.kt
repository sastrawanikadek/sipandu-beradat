package com.sipanduberadat.petugas.adapters

import androidx.fragment.app.Fragment
import androidx.fragment.app.FragmentManager
import androidx.fragment.app.FragmentPagerAdapter
import com.sipanduberadat.petugas.fragments.HandledReportFragment
import com.sipanduberadat.petugas.fragments.UnhandleReportFragment

class MainReportViewPagerAdapter(fragmentManager: FragmentManager):
        FragmentPagerAdapter(fragmentManager, BEHAVIOR_RESUME_ONLY_CURRENT_FRAGMENT) {
    private val fragments = listOf(
            UnhandleReportFragment(),
            HandledReportFragment()
    )

    override fun getCount(): Int {
        return fragments.size
    }

    override fun getItem(position: Int): Fragment {
        return fragments[position]
    }

    override fun getPageTitle(position: Int): CharSequence {
        return if (position == 0) "Belum Ditangani" else "Ditangani"
    }
}