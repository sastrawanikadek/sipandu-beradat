package com.sipanduberadat.guest.adapters

import androidx.fragment.app.Fragment
import androidx.fragment.app.FragmentManager
import androidx.fragment.app.FragmentPagerAdapter
import com.sipanduberadat.guest.fragments.ReportDetailInfoFragment
import com.sipanduberadat.guest.fragments.ReportDetailPecalangFragment
import com.sipanduberadat.guest.fragments.ReportDetailPetugasFragment

class ReportDetailViewPagerAdapter(fragmentManager: FragmentManager): FragmentPagerAdapter(fragmentManager,
        BEHAVIOR_RESUME_ONLY_CURRENT_FRAGMENT) {
    private val fragments = listOf(
            ReportDetailInfoFragment(),
            ReportDetailPecalangFragment(),
            ReportDetailPetugasFragment()
    )

    override fun getCount(): Int {
        return fragments.size
    }

    override fun getItem(position: Int): Fragment {
        return fragments[position]
    }

    override fun getPageTitle(position: Int): CharSequence {
        return when (position) {
            0 -> "Information"
            1 -> "Pecalang"
            else -> "Officer"
        }
    }
}